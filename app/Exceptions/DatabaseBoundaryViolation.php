<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A MeTag API route tried to use the MART database. See DatabaseBoundary.
 */
class DatabaseBoundaryViolation extends RuntimeException {}
