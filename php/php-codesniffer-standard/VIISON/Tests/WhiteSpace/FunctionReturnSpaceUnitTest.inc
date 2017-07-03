<?php

function testA()
{
    return;
}

function testB()
{
    //
    return;
}

function testC()
{
    /**/
    return;
}

function testD()
{
    //
    // Bla
    //
    return;
}

function testE()
{
    /**
     * Bla
     */
    return;
}

function testF()
{
    $foo = 'bar';

    return;
}

function testG()
{
    $foo = 'bar';

    //
    // Bla
    //
    return;
}

function testH()
{
    $foo = 'bar';

    /**
     * Bla
     */
    return;
}

function testI()
{
    $foo = 'bar';
    return;
}

function testJ()
{
    $foo = 'bar';
    //
    // Bla
    //
    return;
}

function testK()
{
    $foo = 'bar';
    /**
     * Bla
     */
    return;
}

switch ($a) {
    case 'a':
        return;
    case 'b':
        //
        return;
    case 'c':
        /**/
        return;
    case 'd':
        //
        // Bla
        //
        return;
    case 'e':
        /**
         * Bla
         */
        return;
    case 'f':
        $foo = 'bar';

        return;
    case 'g':
        $foo = 'bar';

        //
        // Bla
        //
        return;
    case 'h':
        $foo = 'bar';

        /**
         * Bla
         */
        return;
    case 'i':
        $foo = 'bar';
        return;
    case 'j':
        $foo = 'bar';
        //
        // Bla
        //
        return;
    case 'k':
        $foo = 'bar';
        /**
         * Bla
         */
        return;
}
