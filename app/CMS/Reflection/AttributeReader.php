<?php

namespace App\CMS\Reflection;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

class AttributeReader
{
    /**
     * Read a specific attribute from a class.
     *
     * @param  ReflectionClass  $reflection
     * @param  string  $attributeClass
     * @return object|null
     */
    public static function readClassAttribute(ReflectionClass $reflection, string $attributeClass): ?object
    {
        $attributes = $reflection->getAttributes($attributeClass);

        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * Read all attributes from a class.
     *
     * @param  ReflectionClass  $reflection
     * @return array
     */
    public static function readAllClassAttributes(ReflectionClass $reflection): array
    {
        $attributes = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $attributes[$attribute->getName()] = $attribute->newInstance();
        }

        return $attributes;
    }

    /**
     * Read a specific attribute from a property.
     *
     * @param  ReflectionProperty  $property
     * @param  string  $attributeClass
     * @return object|null
     */
    public static function readPropertyAttribute(ReflectionProperty $property, string $attributeClass): ?object
    {
        $attributes = $property->getAttributes($attributeClass);

        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * Read all attributes from a property.
     *
     * @param  ReflectionProperty  $property
     * @return array
     */
    public static function readAllPropertyAttributes(ReflectionProperty $property): array
    {
        $attributes = [];

        foreach ($property->getAttributes() as $attribute) {
            $attributes[$attribute->getName()] = $attribute->newInstance();
        }

        return $attributes;
    }

    /**
     * Check if class has a specific attribute.
     *
     * @param  ReflectionClass  $reflection
     * @param  string  $attributeClass
     * @return bool
     */
    public static function classHasAttribute(ReflectionClass $reflection, string $attributeClass): bool
    {
        return ! empty($reflection->getAttributes($attributeClass));
    }

    /**
     * Check if property has a specific attribute.
     *
     * @param  ReflectionProperty  $property
     * @param  string  $attributeClass
     * @return bool
     */
    public static function propertyHasAttribute(ReflectionProperty $property, string $attributeClass): bool
    {
        return ! empty($property->getAttributes($attributeClass));
    }

    /**
     * Get attribute arguments without instantiating.
     *
     * @param  ReflectionAttribute  $attribute
     * @return array
     */
    public static function getAttributeArguments(ReflectionAttribute $attribute): array
    {
        return $attribute->getArguments();
    }

    /**
     * Find all classes in a namespace that have a specific attribute.
     *
     * @param  string  $namespace
     * @param  string  $attributeClass
     * @param  string  $basePath
     * @return array
     */
    public static function findClassesWithAttribute(string $namespace, string $attributeClass, string $basePath): array
    {
        $classes = [];
        $path = $basePath.'/'.str_replace('\\', '/', $namespace);

        if (! is_dir($path)) {
            return $classes;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace([$basePath.'/', '.php'], '', $file->getPathname());
            $className = str_replace('/', '\\', $relativePath);

            if (! class_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);

                if (self::classHasAttribute($reflection, $attributeClass)) {
                    $classes[] = $className;
                }
            } catch (\ReflectionException $e) {
                // Skip classes that can't be reflected
                continue;
            }
        }

        return $classes;
    }

    /**
     * Get all properties with a specific attribute.
     *
     * @param  ReflectionClass  $reflection
     * @param  string  $attributeClass
     * @return array
     */
    public static function getPropertiesWithAttribute(ReflectionClass $reflection, string $attributeClass): array
    {
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            if (self::propertyHasAttribute($property, $attributeClass)) {
                $properties[] = $property;
            }
        }

        return $properties;
    }

    /**
     * Convert attribute instance to array.
     *
     * @param  object  $attributeInstance
     * @return array
     */
    public static function attributeToArray(object $attributeInstance): array
    {
        $reflection = new ReflectionClass($attributeInstance);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $data = [];

        foreach ($properties as $property) {
            $data[$property->getName()] = $property->getValue($attributeInstance);
        }

        return $data;
    }
}
