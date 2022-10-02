<?php

declare(strict_types=1);

namespace Domain\Stores\DTOs;

use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MappableContract;
use Webmozart\Assert\Assert;

class StockData implements MappableContract
{
    public function __construct(
        public readonly string $title,
        public readonly UriInterface $link,
        public readonly Store $store,
        public readonly ?Price $price,
        public readonly bool $available,
        public readonly string $sku,
        public readonly ?string $imagePath = null,
    ) {
        Assert::minLength($title, 2);
        Assert::minLength($sku, 2);
        if ($imagePath !== null) {
            Assert::fileExists($imagePath);
            Assert::isArray(getimagesizefromstring(File::get($imagePath)));
        }
    }


    public static function generateFake(array $extra = []): self
    {
        $image = imagecreate(200, 200);
        imagejpeg($image, storage_path('app/tmp/test.jpg'));
        // TODO: delete the tmp path and see what happens

        $data = array_merge([
            'title' => '::random-title::',
            'link' => new Uri('https://example.com/skuhere'),
            'store' => Arr::random(Store::cases()),
            'price' => new Price(111, Arr::random(Currency::cases())),
            'available' => true,
            'sku' => '::random-sku::',
            'imagePath' => storage_path('app/tmp/test.jpg'),
        ], $extra);

        return new StockData(
            $data['title'],
            $data['link'],
            $data['store'],
            $data['price'],
            $data['available'],
            $data['sku'],
            $data['imagePath'],
        );
    }
}
