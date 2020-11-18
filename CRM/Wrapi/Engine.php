<?php

/**
 * WrAPI Engine
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Engine
{
    /**
     * Request data
     *
     * @var array|string|null
     */
    protected $requestData;

    /**
     * IO Processor
     *
     * @var \CRM_Wrapi_Processor_Base|null $processor
     */
    protected ?CRM_Wrapi_Processor_Base $processor;

    /**
     * Authenticator
     *
     * @var \CRM_Wrapi_Authenticator|null
     */
    protected ?CRM_Wrapi_Authenticator $authenticator;

    /**
     * CRM_Wrapi_Engine constructor
     */
    public function __construct()
    {
        $this->processor     = null;
        $this->authenticator = null;
        $this->requestData   = null;
    }

    /**
     * Run Engine
     */
    public function run(): void
    {
        try {
            // Detect content-type & instantiate correct processor
            $this->createProcessor(CRM_Wrapi_Processor_Base::detectContentType());

            // Process input
            $this->requestData = $this->processor->processInput();

            // Request now parsed --> authenticate
            $this->createAuthenticator($this->processor);
            $this->authenticator->authenticate($this->requestData['site_key'], $this->requestData['user_key']);

            $this->processor->output($this->requestData);
        } catch (CRM_Core_Exception $ex) {
            http_response_code(500);
            $this->processor->error((string)$ex, false);
        } catch (Throwable $error) {
            http_response_code(500);
            $this->processor->error($error->getMessage(), false);
        }
    }

    /**
     * Create Processor
     *
     * @param  string  $processor_class  Processor class name
     */
    protected function createProcessor(string $processor_class): void
    {
        $this->processor = new $processor_class();
    }

    /**
     * Create Authenticator
     *
     * @param  \CRM_Wrapi_Processor_Base  $processor
     */
    protected function createAuthenticator(CRM_Wrapi_Processor_Base $processor): void
    {
        $this->authenticator = new CRM_Wrapi_Authenticator($processor);
    }
}
