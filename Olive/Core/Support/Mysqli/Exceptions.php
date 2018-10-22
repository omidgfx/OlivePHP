<?php namespace Olive\Support\Exceptions;

use Olive\Exceptions\OliveException;

class MySQLiException extends OliveException {

}

class MySQLiConditionException extends MySQLiException {

}
class MySQLiAdaptingException extends MySQLiException{

}

class MySQLiRecordException extends MySQLiException{

}