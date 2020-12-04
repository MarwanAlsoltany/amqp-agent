<?php

namespace MAKS\AmqpAgent\Tests\Mocks;

final class PropertiesMock
{
    // PropertiesMock

    public function __construct($staticProp = 'STATIC', $privateProp = 'PRIVATE', $protectedProp = 'PROTECTED', $publicProp = 'PUBLIC')
    {
        $this::$staticProp = $staticProp;
        $this->privateProp = $privateProp;
        $this->protectedProp = $protectedProp;
        $this->publicProp = $publicProp;
    }

    public const CONST_PROP = 'CONST';
    public static $staticProp = 'STATIC';
    private $privateProp = 'PRIVATE';
    protected $protectedProp = 'PROTECTED';
    public $publicProp = 'PUBLIC';
}
