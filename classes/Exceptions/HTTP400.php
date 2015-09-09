<?php

namespace Phalcana\Exceptions;


use \Phalcon\Mvc\Dispatcher;

/**
 * This class is primarily for sending back validation messages
 *
 * @package     Phalcana
 * @category    Exceptions
 * @author      Neil Brayfield
 */
class HTTP400 extends HTTP
{
    protected $code = 400;

    protected $message = "Bad Request";




    /**
     * Does any encoding on the message
     *
     * @return  void
     **/
    protected function serialize($value)
    {

        if (is_array($value) && isset($value['errors'])) {
            return $value;
        }

        if (is_object($value) && property_exists($value, 'errors')) {
            return $value;
        }

        $message = "";
        $output = array();
        foreach ($value as $key => $item) {
            if (
                $item instanceof \Phalcon\Mvc\Model\Message ||
                $item instanceof \Phalcon\Validation\Message
                ) {

                if ($item->getField() == "") {
                    $message = $item->getMessage();
                } else {
                    $output[$item->getField()] = $item->getMessage();
                }

            } else {
                $output[$key] = $item;
            }
        }

        return array('errors' => $output,'message' => $message);
    }
}
