<?php

/**
* Class ResponseCodes provides constants for the return
* to the route controller class.
*/
class ResponseCodes {

    const OK = 200;
    const CREATED = 201;
    const NO_MATCH = 901; # no real response code
    const BAD_REQUEST = 400;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const SERVER_ERROR = 500;

}
