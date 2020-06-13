<?php

namespace MAKS\AmqpAgent\Test\Helper;

use MAKS\AmqpAgent\TestCase;
use MAKS\AmqpAgent\Helper\Serializer;
use MAKS\AmqpAgent\Exception\SerializerViolationException;

class SerializerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializer;
    private $testData;

    public function setUp(): void
    {
        parent::setUp();
        $this->serializer = new Serializer();
        $this->testData = [
            'serilizer' => 'test'
        ];
        $this->testDataPhp = 'a:1:{s:9:"serilizer";s:4:"test";}';
        $this->testDataJson = '{"serilizer":"test"}';
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->serializer);
    }

    public function testSerializerWhenSerilizeMethodIsCalled()
    {
        $processedJson = $this->serializer->serialize($this->testData, 'jSoN');
        $this->assertEquals($processedJson, $this->testDataJson);

        $processedPhp = $this->serializer->serialize($this->testData, 'phP');
        $this->assertEquals($processedPhp, $this->testDataPhp);
    }

    public function testSerializerWhenUnserilizeMethodIsCalled()
    {
        $processedJson = $this->serializer->unserialize($this->testDataJson, 'json');
        $this->assertEquals($processedJson, $this->testData);

        $processedPhp = $this->serializer->unserialize($this->testDataPhp, 'php');
        $this->assertEquals($processedPhp, $this->testData);
    }

    public function testSerializerGettersAndSetters()
    {
        $this->serializer->setData($this->testData);
        $this->serializer->setType('JSON');
        $this->assertEquals($this->testData, $this->serializer->getData());
        $this->assertEquals('JSON', $this->serializer->getType());
        $this->assertEquals($this->testDataJson, $this->serializer->getSerialized());

        $this->serializer->setData($this->testDataPhp);
        $this->serializer->setType('PHP');
        $this->assertEquals($this->testData, $this->serializer->getUnserialized());
    }

    public function testSerializerViolationExceptionIsRaisedWhenUnsuppotedTypeIsProvidedToSerialize()
    {
        $this->expectException(SerializerViolationException::class);
        $error = $this->serializer->serialize($this->testData, 'UNKNOWN');
    }

    public function testSerializerViolationExceptionIsRaisedWhenUnsuppotedTypeIsProvidedToUnserialize()
    {
        $this->expectException(SerializerViolationException::class);
        $error = $this->serializer->unserialize($this->testDataPhp, 'UNKNOWN');
    }

    public function testSerializerViolationExceptionIsRaisedWhenTheObjectIsCalledAsAFunction()
    {
        $serializer = $this->serializer;
        $this->expectException(SerializerViolationException::class);
        $error = $serializer('UNKNOWN', 'UNKNOWN');
    }

    public function testSerializerWhenTheObjectIsCalledAsAFunction()
    {
        $serializer = $this->serializer;

        $serializedJsonDataToTest = $serializer($this->testData, 'JSON');
        $this->assertEquals($serializedJsonDataToTest, $this->testDataJson);

        $serializedPhpDataToTest = $serializer($this->testData, 'PHP');
        $this->assertEquals($serializedPhpDataToTest, $this->testDataPhp);

        $unserializedJsonDataToTest = $serializer($this->testDataJson, 'JSON');
        $this->assertEquals($this->testData, $unserializedJsonDataToTest);

        $unserializedPhpDataToTest = $serializer($this->testDataPhp, 'PHP');
        $this->assertEquals($this->testData, $unserializedPhpDataToTest);
    }
}
