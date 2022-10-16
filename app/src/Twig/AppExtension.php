<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('text_by_percent', [$this, 'textByPercent']),
        ];
    }

    public function textByPercent(string $text, int $percent = 50, int $minLength = 50): string
    {
        if ($percent > 1 && $percent <= 100) {
            $lenSource = mb_strlen($text);

            if ($lenSource < $minLength) {
                $lenCut = $lenSource;
            } else {
                $lenCut = (int)($lenSource * $percent / 100);

                if ($lenCut < $minLength) {
                    $lenCut = $minLength;
                }
            }

            return mb_substr($text, 0, $lenCut);
        }

        $message = sprintf('Percent value of part text must be between 1 and 100. Yor values is %s', $percent);

        throw new \UnexpectedValueException($message);
    }
}
