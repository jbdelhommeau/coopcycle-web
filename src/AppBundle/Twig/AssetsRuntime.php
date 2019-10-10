<?php

namespace AppBundle\Twig;

use Twig\Extension\RuntimeExtensionInterface;
use League\Flysystem\MountManager;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class AssetsRuntime implements RuntimeExtensionInterface
{
    private $uploaderHelper;

    public function __construct(
        StorageInterface $storage,
        MountManager $mountManager,
        PropertyMappingFactory $propertyMappingFactory,
        CacheManager $cacheManager)
    {
        $this->storage = $storage;
        $this->mountManager = $mountManager;
        $this->propertyMappingFactory = $propertyMappingFactory;
        $this->cacheManager = $cacheManager;
    }

    public function asset($obj, string $fieldName, string $className = null): ?string
    {
        $mapping = $this->propertyMappingFactory->fromField($obj, $fieldName, $className);

        $fileSystem = $this->mountManager->getFilesystem($mapping->getUploadDestination());

        $uri = $this->storage->resolveUri($obj, $fieldName, $className);

        if (!$uri) {
            return null;
        }

        return $this->cacheManager->getBrowserPath($uri, 'store_thumbnail');
    }
}
