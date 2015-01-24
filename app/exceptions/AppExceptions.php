<?php

namespace App\Exceptions;

use Nette;


/**
 * @desc try to insert duplicate value to fields with unique key
 * Class DuplicateEntryException
 * @package App\Exceptions
 */
class DuplicateEntryException extends \Exception {}

/**
 * @desc user have not premission to view or do something
 * Class AccesDeniedException
 * @package App\Exceptions
 */
class AccesDeniedException extends \Exception {}

