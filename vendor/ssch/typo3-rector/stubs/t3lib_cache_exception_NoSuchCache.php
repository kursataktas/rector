<?php

namespace RectorPrefix20211009;

if (\class_exists('t3lib_cache_exception_NoSuchCache')) {
    return;
}
class t3lib_cache_exception_NoSuchCache
{
}
\class_alias('t3lib_cache_exception_NoSuchCache', 't3lib_cache_exception_NoSuchCache', \false);
