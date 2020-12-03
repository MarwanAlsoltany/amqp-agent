<?php

namespace MAKS\AmqpAgent\Tests\Mocks;

final class MethodsMock
{
    // MethodsMock

    private function privateMethod($string = '')
    {
        return 'PRIVATE: ' . $string;
    }
    protected function protectedMethod($string = '')
    {
        return 'PROTECTED: ' . $string;
    }
    public function publicMethod($string = '')
    {
        return 'PUBLIC: ' . $string;
    }
    public function exception()
    {
        throw new \Exception('Test!');
    }
}
