<?php

namespace Ynlo\GraphQLBundle\Util;

abstract class ElasticUtil
{
    public static function escapeReservedChars($string)
    {
        $regex = "/[\\+\\-\\=\\&\\|\\!\\(\\)\\{\\}\\[\\]\\^\\\"\\~\\*\\<\\>\\?\\:\\\\\\/]/";

        return preg_replace($regex, addslashes('\\$0'), $string);
    }
}