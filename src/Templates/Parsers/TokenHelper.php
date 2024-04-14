<?php

namespace WebImage\BlockManager\Templates\Parsers;

use WebImage\BlockManager\Templates\Lexers\TemplateLexer;
use WebImage\BlockManager\Templates\Lexers\Token;

class TokenHelper
{
    public static function checkPattern(TemplateLexer $lexer, array $pattern): bool
    {
        $nextTokens = self::getNextTokens($lexer, count($pattern));

        for($i=0, $j=count($pattern); $i < $j; $i++) {
            if ($nextTokens[$i]->type === null || $pattern[$i] != $nextTokens[$i]->type) return false;
        }

        return true;
    }

    /**
     * Use callback to evaluate each token to determine whether the token is the one we are looking for.  If so, return the position, otherwise return NULL
     * Useful when we are trying to find a series of patterns.
     * Example: The current character is "{" and we want to find "}" but there are other "{" in the interum that are opened and closed... therefore we want to find the corresponding "}"
     * @param TemplateLexer $lexer
     * @param callable $findMatch
     * @return int|null
     */
    public static function findMatch(TemplateLexer $lexer, callable $findMatch): ?int
    {
        $offset = 1;
        do {
//            if ($offset == 0) $token = $lexer->token;
//            else
                if ($offset == 1) $token = $lexer->lookahead;
            else $token = $lexer->peek();

            if ($token !== null) {
                $result = call_user_func($findMatch, $token, $offset);
                if (!is_bool($result)) throw new \InvalidArgumentException('findMatch callback must return boolean');
                if ($result === true) return $token->position;
            }

            $offset ++;
        } while ($token !== null);

        return null;
    }

    public static function getTokenInXPositions(TemplateLexer $lexer, int $position): ?Token
    {
        if ($position < 0) throw new \InvalidArgumentException('Position must be greater than zero');
        if ($position == 0) return $lexer->token;
        else if ($position == 1) return $lexer->lookahead;
        else {
            for($i=2; $i <= $position; $i++) {
                $token = $lexer->peek();
            }
        }
        $lexer->resetPeek();
        return $token;
    }

    /**
     * @param TemplateLexer $lexer
     * @param int $num
     * @return Token[]
     */
    private static function getNextTokens(TemplateLexer $lexer, int $num): array
    {
        $tokens = [];

        if ($num > 0) $tokens[] = $lexer->token;
        if ($num > 1) $tokens[] = $lexer->lookahead;
        if ($num > 2) {
            for($n=2; $n <= $num; $n++) {
                $tokens[] = $lexer->peek();
            }
        }

        return $tokens;
    }
}
