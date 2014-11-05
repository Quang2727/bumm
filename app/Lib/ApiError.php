<?php
App::uses('ErrorHandler', 'Error');

class ApiError extends ErrorHandler {
    public static function handleError($code, $description, $file = null, $line = null, $context = null) {
        list($error, $log) = self::mapErrorCode($code);
        $message = $error . ' (' . $code . '): ' . $description . ' in [' . $file . ', line ' . $line . ']';
        if (!empty($errorConfig['trace'])) {
            $trace = Debugger::trace(array(
                'start' => 1,
                'format' => 'log'
            ));
            $message .= "\nTrace:\n" . $trace . "\n";
        }

        CakeLog::write($log, $message);
        self::error(__('There is an error occurred.'));
    }

    public static function handleException(Exception $exception) {
        $config = Configure::read('Exception');
        self::_log($exception, $config);

        self::error(__('Bad request.'));
    }

    protected static function error($message = NULL) {
        header('Content-type: text/json; charset=utf-8');
        header('HTTP/1.1 400 Bad Request', true, 400);

        echo json_encode(array(
            'error' => true,
            'message' => $message
        ));

        exit;
    }
}