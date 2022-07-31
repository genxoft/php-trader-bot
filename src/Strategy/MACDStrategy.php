<?php

namespace App\Strategy;

use App\BinanceApiClient\BinanceApiInterface;
use App\BinanceApiClient\Side;
use App\BinanceApiClient\OrderType;
use App\Entity\Deal;
use App\Exception\RuntimeException;
use App\Repository\DealRepositoryInterface;
use App\Worker\TaskInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

final class MACDStrategy implements TaskInterface
{
    private ?Deal $lastDeal = null;

    private readonly DealRepositoryInterface $dealRepository;

    private readonly BinanceApiInterface $binanceApi;

    private readonly LoggerInterface $logger;

    /**
     * @param ContainerInterface $ci
     * @param string $name
     * @param string $symbol
     * @param int $klinesInterval in minutes
     * @param string $capacity
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(
        readonly private ContainerInterface $ci,
        readonly private string $name,
        readonly private string $symbol,
        readonly private int $klinesInterval,
        readonly private string $capacity
    ) {
        if (!function_exists('trader_macd')) {
            throw new \RuntimeException("php trader library required");
        }

        $this->dealRepository = $this->ci->get(DealRepositoryInterface::class);
        $this->binanceApi = $this->ci->get(BinanceApiInterface::class);
        if ($this->ci->has(LoggerInterface::class)) {
            $this->logger = $this->ci->get(LoggerInterface::class);
        } else {
            $this->logger = new NullLogger();
        }
    }


    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function run(int $lastRunTime): bool
    {
        if ($this->lastDeal === null) {
            // Take last deal from database if it`s null
            $this->lastDeal = $this->dealRepository->findLast($this->name, 1)[0] ?? null;
        }

        if ($this->lastDeal === null) {
            // If it`s first deal in strategy set last SIDE in SELL
            $lastSide = 'SELL';
        } else {
            $lastSide = $this->lastDeal->side;
        }

        $klineCollection = $this->binanceApi->getKlines($this->symbol, $this->klinesInterval . 'm', null, null, 100);

        $closes = $klineCollection->orderBy('openTime')->getColumn('close');

        if (!function_exists('trader_macd')) {
            throw new \RuntimeException("php trader library required");
        }
        $macd = \trader_macd($closes, 12, 26, 9);

        $lastMACDDivergence = array_pop($macd[2]);

        $this->logger->debug("{name}: Last MACD Divergence: {lastMACDDivergence}", [
            'name'                  => $this->name,
            'lastMACDDivergence'    => $lastMACDDivergence
        ]);

        $dealSide = $this->makeDecision($lastSide, $lastMACDDivergence);
        if ($dealSide !== null) {
            $deal = $this->deal($dealSide);
        } else {
            return true;
        }

        if ($deal->side === 'SELL') {
            if ($this->lastDeal !== null) {
                $deal->profit = $this->calculateProfit($deal->cummulativeQuoteQty, $this->lastDeal->cummulativeQuoteQty);
                $profitPercent = round(100 / ($this->lastDeal->cummulativeQuoteQty / $deal->profit), 2);
            } else {
                $deal->profit = '0';
                $profitPercent = 0;
                $this->logger->warning("Previous BUY deal not found");
            }

            $this->logger->log(
                $profitPercent > 0 ? LogLevel::INFO : LogLevel::ALERT,
                "{name} SELL {symbol} {origQty}>{cummulativeQuoteQty} Profit: {profit} ({profitPercent}%)",
                [
                    'name'                  => $this->name,
                    'symbol'                => $deal->symbol,
                    'origQty'               => $deal->origQty,
                    'cummulativeQuoteQty'   => $deal->cummulativeQuoteQty,
                    'profit'                => $deal->profit,
                    'profitPercent'         => $profitPercent,
                ]
            );
        } elseif ($deal->side === 'BUY') {
            $this->logger->info(
                "{name} BUY {symbol} {cummulativeQuoteQty}>{origQty}",
                [
                    'name'                  => $this->name,
                    'symbol'                => $deal->symbol,
                    'origQty'               => $deal->origQty,
                    'cummulativeQuoteQty'   => $deal->cummulativeQuoteQty,
                ]
            );
        } else {
            throw new RuntimeException(sprintf("Invalid side %s", $deal->side));
        }

        $this->lastDeal = $deal;

        return true;
    }

    private function calculateProfit(string $sellQty, string $buyQty): string
    {
        return bcsub($sellQty, $buyQty, 6);
    }

    /**
     * @param string $lastSide
     * @param string $lastMACDDivergence
     * @return Deal|null
     * @throws RuntimeException
     */
    private function makeDecision(string $lastSide, string $lastMACDDivergence): Side|null
    {
        if ($lastSide === 'SELL') {
            // If last deal was SELL wait for positive MACD Divergence
            if ($lastMACDDivergence > 0) {
                return Side::BUY;
            }
        } elseif ($lastSide === 'BUY') {
            // If last deal was BUY wait for negative MACD Divergence
            if ($this->lastDeal === null) {
                throw new RuntimeException(sprintf("Last deal is null when side is %s", $lastSide));
            }
            if ($lastMACDDivergence < 0) {
                return Side::SELL;
            }
        } else {
            throw new RuntimeException(sprintf("Invalid side %s", $lastSide));
        }
        return null;
    }

    /**
     * @param Side $side
     * @return Deal
     */
    private function deal(Side $side): Deal
    {
        $dealData = $this->binanceApi->postOrder(
            $this->symbol,
            $side,
            OrderType::MARKET,
            null,
            null,
            $this->capacity
        );
        $deal = new Deal($dealData);
        $deal->name = $this->name;
        $this->dealRepository->save($deal);
        return $deal;
    }
}
