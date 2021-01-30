<?php
namespace Draebersnegl\PhpSerialModbus;
/**
 * Serial port control class
 *
 * THIS PROGRAM COMES WITH ABSOLUTELY NO WARRANTIES !
 * USE IT AT YOUR OWN RISKS !
 *
 * @copyright under GPL 3 licence
 */
 
class PhpSerialDecoder
{
    const ENDIAN_BIG = 0;
    const ENDIAN_LITTLE = 1;

    private $bin;

    public function __construct($string, $as_hex = true) {
        if($as_hex){
            $this->bin = $this->hex2bin($string);
        }else{
            $this->bin = $string;
        }
    }

    /**
     * convert hex-string into bin-string
     * @param string $data
     * @return string
     */
    private function hex2bin($data){
        $encoded = '';
        $data_arr = str_split($data, 2);

        foreach($data_arr as $val){
            $binary = base_convert($val, 16, 2);
            $encoded .= str_pad($binary, 8, '0', STR_PAD_LEFT);
        }
        return $encoded;
    }

    /**
     * get integer value out of current binary
     * @param integer $start offset
     * @param integer $length length
     * @return integer
     */
    public function getValueFromBits($start, $length = null){
        return base_convert($this->getBinarySlice($start, $length), 2, 10);
    }

    /**
     * get particular piece of current binary
     * @param integer $start
     * @param integer $length
     * @return string
     */
    public function getBinarySlice($start, $length = null){
        if($length){
            return substr($this->bin, $start, $length);
        }else{
            return substr($this->bin, $start);
        }
    }

    /**
     * bits count
     * @return type
     */
    public function bits(){
        return strlen($this->bin);
    }

    /**
     * get 32-bit float value at particular offset at specific endian type
     * @param integer $offset offset
     * @return float
     */
    public function getFloatFromBits($offset, $mode = self::ENDIAN_BIG){
        if($mode === self::ENDIAN_BIG){
            $sign = $this->getBinarySlice($offset, 1);
            $exp = $this->getBinarySlice($offset + 1, 8);
            $mantissa = "1" . $this->getBinarySlice($offset + 9, 23);
        }else{
            $sign = $this->getBinarySlice($offset + 24, 1);
            $exp = $this->getBinarySlice($offset + 25, 7).$this->getBinarySlice($offset + 16, 1);
            $mantissa = "1" . $this->getBinarySlice($offset + 17, 7) . $this->getBinarySlice($offset + 8, 8) . $this->getBinarySlice($offset, 8);
        }

        $mantissa = str_split($mantissa);
        $exp = bindec($exp) - 127;
        $base = 0;

        for ($i = 0; $i < 24; $i++) {
            $base += (1 / pow(2, $i))*$mantissa[$i];
        }
        return $base * pow(2, $exp) * ($sign*-2+1);
    }
} 
