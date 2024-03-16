<?php

namespace App\Business;

use Illuminate\Http\Request;
use Exception;
use ModbusTcpClient\Network\BinaryStreamConnection;
use ModbusTcpClient\Packet\ModbusFunction\ReadInputRegistersRequest;
use ModbusTcpClient\Packet\ResponseFactory;

class ModBuss
{
    public static $connection = null;
    public static $startAddress = 256;
    public static $quantity = 6;
    public static $unitID = 1;
    public static $port = 502;
    public static $host = "192.168.0.201"; //192.168.0.251

    public static function connect(){
        self::$connection = BinaryStreamConnection::getBuilder()
            ->setPort(502)
            ->setHost('127.0.0.1') //192.168.0.251
            ->build();
        //---
    }

    public static function testConnection(){
        try {
            self::$connection = BinaryStreamConnection::getBuilder()
                ->setHost('192.168.254.15')
                ->setPort(502)
                ->build();

            self::$connection->connect();

            $message = 'Modbus TCP ';

            // 关闭连接
            self::$connection->close();

            return $message;
        } catch (\Exception $e) {
            return 'Modbus TCP 连接失败：' . $e->getMessage();
        }
    }

    public static function ReadInputRegisters(){
        self::$connection = BinaryStreamConnection::getBuilder()
            ->setPort(self::$port)
            ->setHost(self::$host)
            ->build();
        //---
        $packet = new ReadInputRegistersRequest(self::$startAddress, self::$quantity, self::$unitID); // NB: Este é um pacote Modbus TCP, não Modbus RTU sobre TCP!
        echo 'Pacote a ser enviado (em hexadecimal): ' . $packet->toHex() . PHP_EOL."<br>";

        try {
            $binaryData = self::$connection->connect()->sendAndReceive($packet);
            echo 'Binário recebido (em hexadecimal):   ' . unpack('H*', $binaryData)[1] . PHP_EOL."<br>";

            /**
             * @var $response ReadInputRegistersResponse
             */
            $response = ResponseFactory::parseResponseOrThrow($binaryData);
            echo 'Pacote analisado (em hexadecimal):     ' . $response->toHex() . PHP_EOL."<br>";
            echo 'Dados analisados ​​do pacote (bytes):' . PHP_EOL."<br>";
            print_r($response->getData());

            foreach ($response as $word) {
                print_r($word->getBytes());
            }
            foreach ($response->asDoubleWords() as $doubleWord) {
                print_r($doubleWord->getBytes());
            }

            // definir o índice interno para corresponder ao endereço inicial para simplificar o acesso ao array
            $responseWithStartAddress = $response->withStartAddress(self::$startAddress);
            print_r($responseWithStartAddress[256]->getBytes()); // use array access to get word
            print_r($responseWithStartAddress->getDoubleWordAt(257)->getFloat());

        } catch (Exception $exception) {
            echo 'Ocorreu uma exceção' . PHP_EOL."<br>";
            echo $exception->getMessage() . PHP_EOL."<br>";
            echo $exception->getTraceAsString() . PHP_EOL."<br>";
        } finally {
            self::$connection->close();
        }
    }
}
