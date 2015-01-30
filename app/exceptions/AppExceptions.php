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
 * Class AccessDeniedException
 * @package App\Exceptions
 */
class AccessDeniedException extends \Exception {}

/**
 * @desc parameter suplied as an argument is wrong (typehint/range)
 * Class AccessDeniedException
 * @package App\Exceptions
 */
class InvalidArgumentException extends \Exception {}



