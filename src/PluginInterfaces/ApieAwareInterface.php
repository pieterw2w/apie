<?php


namespace W2w\Lib\Apie\PluginInterfaces;

use W2w\Lib\Apie\Apie;

interface ApieAwareInterface
{
    /**
     * @param Apie $apie
     * @return mixed
     */
    public function setApie(Apie $apie);
}
