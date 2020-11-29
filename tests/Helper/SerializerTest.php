<?php

namespace MAKS\AmqpAgent\Tests\Helper;

use MAKS\AmqpAgent\Tests\TestCase;
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
            'serializer' => 'test'
        ];
        $this->testDataPhp = 'a:1:{s:10:"serializer";s:4:"test";}';
        $this->testDataJson = '{"serializer":"test"}';
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->serializer);
    }

    public function testSerializerWhenSerializeMethodIsCalled()
    {
        $processedJson = $this->serializer->serialize($this->testData, 'jSoN');
        $this->assertEquals($processedJson, $this->testDataJson);

        $processedPhp = $this->serializer->serialize($this->testData, 'phP');
        $this->assertEquals($processedPhp, $this->testDataPhp);

        $processedPhp = $this->serializer->serialize($this->testData, 'PHP', true);
        $this->assertEquals($processedPhp, $this->testDataPhp);
    }

    public function testSerializerWhenUnserializeMethodIsCalled()
    {
        $processedJson = $this->serializer->unserialize($this->testDataJson, 'json');
        $this->assertEquals($processedJson, $this->testData);

        $processedPhp = $this->serializer->unserialize($this->testDataPhp, 'php');
        $this->assertEquals($processedPhp, $this->testData);

        $processedPhp = $this->serializer->deserialize($this->testDataPhp, 'php');
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

        $this->serializer->setStrict(false);
        $this->assertFalse($this->serializer->isStrict());
        $this->serializer->setStrict(true);
        $this->assertTrue($this->serializer->isStrict());
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

    public function testSerializerViolationExceptionIsRaisedWhenMalformedPhpDataIsProvidedToUnserialize()
    {
        $this->expectException(SerializerViolationException::class);
        $wrongPhp = 'a:1:{s:10:serializer";s:4:"test";}';
        $error = $this->serializer->unserialize($wrongPhp, 'PHP', true);
    }

    public function testSerializerViolationExceptionIsRaisedWhenMalformedJsonDataIsProvidedToUnserialize()
    {
        $this->expectException(SerializerViolationException::class);
        $wrongJson = '{serializer":"test"}';
        $error = $this->serializer->unserialize($wrongJson, 'JSON', true);
    }

    public function testSerializerViolationExceptionIsRaisedWhenTheObjectIsCalledAsAFunction()
    {
        $serializer = $this->serializer;
        $this->expectException(SerializerViolationException::class);
        $error = $serializer('UNKNOWN', 'UNKNOWN');
    }

    public function testSerializerViolationExceptionIsRaisedWhenTheObjectIsCalledAsAFunctionWithMalformData()
    {
        $serializer = $this->serializer;
        $this->expectException(SerializerViolationException::class);
        $error = $serializer('{serializer":"test,}', 'JSON');
    }

    public function testSerializerWhenTheObjectIsCalledAsAFunction()
    {
        $serializer = $this->serializer;

        $serializedJsonDataToTest = $serializer($this->testData, 'JSON', true);
        $this->assertEquals($serializedJsonDataToTest, $this->testDataJson);

        $unserializedJsonDataToTest = $serializer($this->testDataJson, 'JSON', true);
        $this->assertEquals($this->testData, $unserializedJsonDataToTest);

        $serializedPhpDataToTest = $serializer($this->testData, 'PHP', true);
        $this->assertEquals($serializedPhpDataToTest, $this->testDataPhp);

        $unserializedPhpDataToTest = $serializer($this->testDataPhp, 'PHP', true);
        $this->assertEquals($this->testData, $unserializedPhpDataToTest);
    }
}
