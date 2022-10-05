<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Stores\Services;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Psr\Http\Message\UriInterface;
use Support\Contracts\StoreContract;

trait StoreContractTests
{
    abstract public function getStoreImplementation(): string;

    abstract public function randomSearchLinkForStore(): UriInterface;

    /** @test **/
    public function a_random_product_fetches_successfully(): void
    {
        // Arrange
        $service = app($this->getStoreImplementation());

        // Act
        $randomUri = $this->randomProductUri();
        $result = $service->product([$randomUri]);

        // Assert
        $this->assertContainsOnlyInstancesOf(StockData::class, $result);
        $this->assertSame((string) $randomUri, (string) $result[0]->link);
        $this->assertSame($result[0]->store->serviceFqcn(), $this->getStoreImplementation());
    }

    /** @test **/
    public function a_product_search_is_successful(): void
    {
        // Arrange
        $service = app($this->getStoreImplementation());

        // Act
        $result = $service->search([$this->randomSearchLinkForStore()]);

        // Assert
        $this->assertContainsOnlyInstancesOf(StockSearchData::class, $result);

        foreach ($result[0]->stocks as $stock) {
            $this->assertSame($this->getStoreImplementation(), $stock->store->serviceFqcn());
        }
    }

    public function randomProductUri(): UriInterface
    {
        /** @var StoreContract $service */
        $service = app($this->getStoreImplementation());
        $products = $service->search([$this->randomSearchLinkForStore()]);

        return $products[0]->stocks->random()->link;
    }
}
