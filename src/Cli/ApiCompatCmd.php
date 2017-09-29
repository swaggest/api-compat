<?php

namespace Swaggest\ApiCompat\Cli;

use Swaggest\ApiCompat\ApiCompat;
use Swaggest\ApiCompat\Change;
use Swaggest\ApiCompat\Path;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Io\Response;

class ApiCompatCmd extends Command
{
    public $originalPath;
    public $newPath;
    public $verbose;


    /**
     * @param Definition $definition
     * @param \stdClass|static $options
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $definition->name = 'api-compat';
        $definition->version = 'v1.0.0';
        $definition->description = 'API compatibility checker for swagger.json, https://github.com/swaggest/api-compat';

        $options->originalPath = Command\Option::create()->setIsUnnamed()->setIsRequired()
            ->setDescription('Path to old (original) swagger.json file');
        $options->newPath = Command\Option::create()->setIsUnnamed()->setIsRequired()
            ->setDescription('Path to new swagger.json file');

        $options->verbose = Command\Option::create()->setDescription('Verbose output');
    }

    public function performAction()
    {
        $originalJson = file_get_contents($this->originalPath);
        if (!$originalJson) {
            $this->response->error('Unable to read ' . $this->originalPath);
            die(1);
        }

        $newJson = file_get_contents($this->newPath);
        if (!$newJson) {
            $this->response->error('Unable to read ' . $this->newPath);
            die(1);
        }

        $ac = new ApiCompat(json_decode($originalJson), json_decode($newJson));
        if ($ac->getBreakingChanges()) {
            if ($this->verbose) {
                $this->showMessages($ac->getBreakingChanges(), $this->response);
            }
            $this->response->error('Breaking changes detected in new swagger schema');
            die(1);
        } else {
            $this->response->success('No breaking changes detected in new swagger schema');
        }

    }

    /**
     * @param Change[] $changes
     * @param Response $response
     */
    public function showMessages($changes, $response)
    {
        foreach ($changes as $breakingChange) {
            $response->error($breakingChange->message . ' at ' . Path::quoteUrldecode($breakingChange->path));
            if ($breakingChange->originalValue) {
                $response->addContent('original: ' . json_encode($breakingChange->originalValue, JSON_UNESCAPED_SLASHES));
            }
            if ($breakingChange->newValue) {
                $response->addContent('new: ' . json_encode($breakingChange->newValue, JSON_UNESCAPED_SLASHES));
            }
        }

    }



}