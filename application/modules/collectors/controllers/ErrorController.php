<?php
class Collectors_ErrorController extends Collectors_Library_Controller
{
    public function errorAction()
    {
        $error = $this->_getParam('error_handler');

        if (!$error || !$error instanceof ArrayObject) {
            $this->sendOutput('You have reached the error page');
            return;
        }

        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $message = 'Page not found';
                break;

            default:
                $message = 'Application error';

                Log::Err($error->exception->getMessage());
                break;
        }

        $this->sendOutput($message);

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->sendOutput($error->exception->getMessage());
        }
    }
}
