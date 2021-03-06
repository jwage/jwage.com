---
title: A cool script for running PHPUnit tests in parallel processes
categories: [articles]
---
<p>At <a href="http://opensky.com" target="_blank">OpenSky</a> we&rsquo;re exploring options for getting better build times. We came across this script found <a href="http://pebblesinthesand.wordpress.com/2008/05/22/a-srcipt-for-running-processes-in-parallel-in-bash/" target="_blank">here</a> for executing commands and running the processes in parallel.</p>

<pre><code>#!/bin/bash
NUM=0
QUEUE=""
MAX_NPROC=2 # default
REPLACE_CMD=0 # no replacement by default
USAGE="A simple wrapper for running processes in parallel.
Usage: `basename $0` [-h] [-r] [-j nb_jobs] command arg_list
    -h      Shows this help
    -r      Replace asterix * in the command string with argument
    -j nb_jobs  Set number of simultanious jobs [2]
 Examples:
    `basename $0` somecommand arg1 arg2 arg3
    `basename $0` -j 3 \"somecommand -r -p\" arg1 arg2 arg3
    `basename $0` -j 6 -r \"convert -scale 50% * small/small_*\" *.jpg"

function queue {
    QUEUE="$QUEUE $1"
    NUM=$(($NUM+1))
}

function regeneratequeue {
    OLDREQUEUE=$QUEUE
    QUEUE=""
    NUM=0
    for PID in $OLDREQUEUE
    do
        if [ -d /proc/$PID  ] ; then
            QUEUE="$QUEUE $PID"
            NUM=$(($NUM+1))
        fi
    done
}

function checkqueue {
    OLDCHQUEUE=$QUEUE
    for PID in $OLDCHQUEUE
    do
        if [ ! -d /proc/$PID ] ; then
            regeneratequeue # at least one PID has finished
            break
        fi
    done
}

# parse command line
if [ $# -eq 0 ]; then #  must be at least one arg
    echo "$USAGE" &gt;&amp;2
    exit 1
fi

while getopts j:rh OPT; do # "j:" waits for an argument "h" doesnt
    case $OPT in
    h)  echo "$USAGE"
        exit 0 ;;
    j)  MAX_NPROC=$OPTARG ;;
    r)  REPLACE_CMD=1 ;;
    \?) # getopts issues an error message
        echo "$USAGE" &gt;&amp;2
        exit 1 ;;
    esac
done

# Main program
echo Using $MAX_NPROC parallel threads
shift `expr $OPTIND - 1` # shift input args, ignore processed args
COMMAND=$1
shift

for INS in $* # for the rest of the arguments
do
    # DEFINE COMMAND
    if [ $REPLACE_CMD -eq 1 ]; then
        CMD=${COMMAND//"*"/$INS}
    else
        CMD="$COMMAND $INS" #append args
    fi
    echo "Running $CMD"

    $CMD &amp;
    # DEFINE COMMAND END

    PID=$!
    queue $PID

    while [ $NUM -ge $MAX_NPROC ]; do
        checkqueue
        sleep 0.4
    done
done
wait # wait for all processes to finish before exit
</code></pre>

<p>Here is a <a href="https://gist.github.com/30055f607fedf2f384d7" target="_blank">gist</a> of the code. Save the above file to a script named parallel and make it executable.</p>

<p>We can now easily use this with PHPUnit to run our tests in parallel processes. If you were to setup 10 groups of tests like this:</p>

<pre><code>/**
 * @group group1
 */
class MyTest
{
    // ...
}
</code></pre>

<p>You could run the groups in parallel processes like this:</p>

<pre><code>$ ./parallel -j 10 -r "phpunit -c opensky --group=*" group1 group2 group3 group4 group5 group6 group7 group8 group9 group10
</code></pre>

<p>So if each group took ~1 minute to run, running them all together would take ~10 minutes, but if you ran it with this script you could get them all done in ~1 minute!</p>

<p>One caveat is we have to figure out a way for each test run inside parallel to use a different configuration, database, etc. so that the tests do not walk on each other and are isolated.</p>
