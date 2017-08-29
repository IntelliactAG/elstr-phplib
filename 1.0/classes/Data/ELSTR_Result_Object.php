<?php
/**
 * Created by PhpStorm.
 * User: sahun
 * Date: 14.08.2017
 * Time: 19:50
 */

class ELSTR_Result_Object
{
    public $data;
    public $messages;

    /**
     * ELSTR_Result_Object constructor.
     * @param $data
     * @param $messages
     */
    public function __construct($data, $messages)
    {
        $this->data = $data;
        $this->messages = $messages;
    }

}