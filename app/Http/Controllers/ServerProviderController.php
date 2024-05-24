<?php

namespace App\Http\Controllers;

use App\Infrastructure\Entities\Image;
use App\Infrastructure\Entities\OperatingSystem;
use App\Infrastructure\Entities\Region;
use App\Infrastructure\Entities\ServerSize;
use App\Infrastructure\ProviderFactory;
use App\Infrastructure\ServerProvider;
use App\Models\Credentials;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class ServerProviderController extends Controller
{
    public function __construct(private ProviderFactory $providerFactory)
    {
    }

    public function regions(Credentials $credentials)
    {
        /** @var ServerProvider */
        $provider = $this->providerFactory->forCredentials($credentials);

        return $provider->findAvailableServerRegions()->mapWithKeys(function (Region $region) {
            return [$region->id => $region->name];
        });
    }

    public function sizes(Credentials $credentials, $region)
    {
        /** @var ServerProvider */
        $provider = $this->providerFactory->forCredentials($credentials);

        return $provider->findAvailableServerSizesByRegion($region)
            ->sortBy(function (ServerSize $serverSize) {
                return $serverSize->monthlyPriceAmount;
            })
            ->mapWithKeys(function (ServerSize $serverSize) {
                return [$serverSize->id => $serverSize->name];
            });
    }

    public function images(Credentials $credentials, $region)
    {
        /** @var ServerProvider */
        $provider = $this->providerFactory->forCredentials($credentials);

        return $provider->findAvailableServerImagesByRegion($region)
            ->filter(function (Image $image) {
                return $image->operatingSystem === OperatingSystem::Ubuntu2204;
            })
            ->mapWithKeys(function (Image $image) {
                return [$image->id => 'Ubuntu 22.04'];
            });
    }
}
