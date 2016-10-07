<?php

namespace App\ValueObjects;

class Package
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $repository;

    /**
     * @var int
     */
    protected $downloads;

    /**
     * @var int
     */
    protected $favers;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return int
     */
    public function getDownloads()
    {
        return number_format($this->downloads);
    }

    /**
     * @return int
     */
    public function getFavers()
    {
        return number_format($this->favers);
    }

    /**
     * @return string
     */
    public function getInstallCommand()
    {
        return sprintf('```composer require %s```', $this->getName());
    }

    /**
     * Constructor made protected in favour of a static constructor
     * Since we can construct the object based on a specific context
     *
     * @param array $formattedArray
     */
    protected function __construct(array $formattedArray)
    {
        foreach ($formattedArray as $property => $value) {
            if (!property_exists(self::class, $property)) {
                continue;
            }

            $this->$property = $value;
        }
    }

    /**
     * Create a new Package object from a deserialized packagist search response
     *
     * @param array $searchResult
     * @return static
     */
    public static function fromSearchResult(array $searchResult)
    {
        return new self($searchResult);
    }

    /**
     * Create a new Package object from a deserialized packagist package details response
     *
     * @param array $packageDetails
     * @return static
     */
    public static function fromPackageDetails(array $packageDetails)
    {
        if (!array_key_exists('package', $packageDetails)) {
            throw new \InvalidArgumentException('Missing package details in response');
        }

        $package = $packageDetails['package'];
        $package['url'] = 'https://packagist.org/packages/' . $package['name'];
        $package['downloads'] = $package['downloads']['total'];

        return new self($package);
    }
}
