<?php

namespace Swaggest\ApiCompat;

use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPointer;

class ApiCompat
{
    private $original;
    private $new;

    /** @var JsonDiff */
    private $jsonDiff;

    /** @var Change[] */
    private $breakingChanges = array();

    public function __construct($original, $new)
    {
        $this->jsonDiff = new JsonDiff($original, $new, JsonDiff::JSON_URI_FRAGMENT_ID + JsonDiff::REARRANGE_ARRAYS);

        $this->original = $original;
        $this->new = $this->jsonDiff->getRearranged();

        $this->checkModifications();
        $this->checkAdditions();
        $this->checkRemovals();
    }

    public function getBreakingChanges()
    {
        return $this->breakingChanges;
    }

    public function getDiff()
    {
        return $this->jsonDiff;
    }


    private function checkAdditions()
    {
        foreach ($this->jsonDiff->getAddedPaths() as $path) {
            $new = JsonPointer::getByPointer($this->new, $path);
            switch (true) {
                case Path::fitsPattern($path, '#/definitions/*/required'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Required constraint added to structure', null, $new);
                    break;
                case Path::fitsPattern($path, '#/paths/*/*/parameters/*'):
                    if (!empty($new->required)) {
                        $this->breakingChanges[$path] =
                            new Change($path, 'Required parameter added', null, $new);
                    } elseif (isset($new->in) && $new->in === 'body') {
                        $this->breakingChanges[$path] = new Change($path, 'Body parameter added', null, $new);
                    }
                    break;
            }
        }
    }

    private function checkRemovals()
    {
        foreach ($this->jsonDiff->getRemovedPaths() as $path) {
            $original = JsonPointer::getByPointer($this->original, $path);
            switch (true) {
                case Path::fitsPattern($path, '#/paths/*'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Path removed', $original);
                    break;
                case Path::fitsPattern($path, '#/paths/*/*'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Method removed', $original);
                    break;
                case Path::fitsPattern($path, '#/paths/*/*/responses/*'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Response for http code removed', $original);
                    break;
                case Path::fitsPattern($path, '#/definitions/*/properties/*'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Structure property removed', $original);
                    break;
            }
        }
    }

    private function checkModifications()
    {
        foreach ($this->jsonDiff->getModifiedPaths() as $path) {
            $original = JsonPointer::getByPointer($this->original, $path);
            $new = JsonPointer::getByPointer($this->new, $path);

            switch (true) {
                case Path::fitsPattern($path, '#/paths/*/*/parameters/*/name'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Missing parameter named ' . $original);
                    break;
                case Path::fitsPattern($path, '#/paths/*/*/parameters/*/required'):
                    if ($new === true) {
                        $this->breakingChanges[$path] =
                            new Change($path, 'Optional parameter became required');
                    }
                    break;
                case Path::fitsPattern($path, '#/paths/*/*/parameters/*/in'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Parameter disposition has changed', $original, $new);
                    break;
                case Path::fitsPattern($path, '#/paths/*/*/parameters/*/type'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Parameter type has changed', $original, $new);
                    break;
                case Path::fitsPattern($path, '#/paths/*/*/parameters/*/schema/...'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Parameter schema has changed', $original, $new);
                    break;
                case Path::fitsPattern($path, '#/paths/*/*/responses/*/schema/%24ref'):
                    $this->breakingChanges[$path] =
                        new Change($path, 'Response schema has changed', $original, $new);
                    break;


            }
        }
    }

}