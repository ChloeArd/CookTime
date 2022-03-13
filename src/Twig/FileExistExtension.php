<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FileExistExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_exist', [$this, 'ifFileExist']),
        ];
    }

    /**
     * Check if a file exists
     * @param string $name
     * @return bool
     */
    public function ifFileExist(string $name): bool
    {
        return file_exists($name);
    }
}
