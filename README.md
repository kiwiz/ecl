ECL
===

[![Build Status](https://travis-ci.org/kiwiz/ecl.svg?branch=master)](https://travis-ci.org/kiwiz/ecl) [![Code Climate](https://codeclimate.com/github/kiwiz/ecl/badges/gpa.svg)](https://codeclimate.com/github/kiwiz/ecl) [![Test Coverage](https://codeclimate.com/github/kiwiz/ecl/badges/coverage.svg)](https://codeclimate.com/github/kiwiz/ecl/coverage)

ECL is a simple query language intended for use with data sources that generate tabulated data. It allows you to query data from these sources and do some limited manipulation. It supports conditional statements, loops, variables and comments. There are almost certainly bugs. Pls report!


Syntax
------

ECL is based on bash's shell syntax. You'll find similar ideas here:

- Comments (`# HACK`).
- Variables (bools, ints, floats, strings, arrays).
- String concatentation (`"app"'le'`).
- Output redirection via `>` and `|`.
- Control flow (`if` and `for`).
And some other stuff I'm forgetting.

The authoritative source for syntax questions is the [grammar](https://github.com/kiwiz/ecl/blob/master/grammar.pegjs). For a gentler introduction, try checking out the examples below.


Example Programs
----------------

```
set type="access_log"; # Define the type we want to query
es:logstash _type:$type > res_a; # Query ES for data and store the results into a variable
if `count(res_a) > 0` { # If we got results...
    # Load up our results and use it in a follow up query.
    # Look for any info_log documents that match any of the `request_uaid`s in our first result set.
    load res_a | es:logstash _type:info_log request_uaid:$_.request_uaid;
}
```

```
set list=["one","two","three"];
for list {
    es:logstash a:$_.value;
}
```


Reference
=========


Comments
--------

```
# This is a comment
```


Statements
----------

### Set ###

```set VAR=VALUE```

The set statement allows you to assign a primitive value to a variable. Has no return value.

`VAR`: The name of the variable.
`VALUE`: The value to set the variable to. Supports bools, ints, floats, strings and arrays.

Example: `set num=10`


### Cond ###

```
if `COND` {
    BRANCH_A
}
```
```
if `COND` {
    BRANCH_A
} else {
    BRANCH_B
}
```

The if statement allows you to branch execution. It accepts an ECL expression which allows you to access any global variables. Returns the value of the winning branch.

`COND`: An SEL expression.
`BRANCH_A`: The truthy branch.
`BRANCH_B`: The falsey branch.

Example: `if `true` { es:logstash url:"/" } else { es:logstash -url:"/" }`


### Loop ###

```
for VAR {
    CODE
}
```

The loop statement allows you to loop over a result. Each iteration of the loop body sets the `_` variable. Returns the value of the loop body.

`VAR`: The result to iterate over.
`CODE`: The code block to execute.

Example: `for res { count }`


### CommandList ###

```
AAA
```
```
AAA | BBB | ...
```
```
AAA | BBB | > VAR_A | CCC | DDD > VAR_B
```

The CommandList is a pipeline of Commands to execute. Output from each Command flows to the next one until it reaches the end. You can copy the output for almost all Commands into a varible. Returns the output from the final Command iff it is not redirected into a variable.

`AAA`,`BBB`,`CCC`,`DDD`: Commands


Commands
--------

### Count ###

```
count
```

Returns the result count for the result set.


### Filter ###

```
filter `EXPR`
```

Filters the result set with the provided expression.

`EXPR`: An SEL expression.

Example: `filter `_['level'] == 'info'``


### Head ###

```
head NUM
```

Returns the first n results.

`NUM`: The max number of results to return.

Example: `head 5`


### Tail ###

```
tail NUM
```

Returns the last n results.

`NUM`: The max number of results to return.

Example: `tail 5`


### Join ###

```
join:TYPE RES=FIELD+FIELD+...
```

Performs an inner join on two result sets.

`TYPE`: The type of join (`inner`, `left`, `right`).
`RES`: The result set to join with.
`FIELD`: The field to join on.

Example: `join res_a=request_uaid`


### Map ###

```
map -FIELD
```
```
map AAA+BBB=CCC
```
```
map FIELD=`EXPR`
```

Map field names and values in the results. You can specify multiple clauses in a single Command and they'll be executed in order.

Example: `map count=`_ + 3` _type=type -_type`


### Search ###

```
es:SRC QUERY
```
```
es:SRC OPTS QUERY | agg:ATYPE AFIELD AOPTS
```

Returns data from ES.

`SRC`: The source (usually `logstash`).
`OPTS`: Search options.
`QUERY`: A query.
`ATYPE`: Aggregation type.
`AOPTS`: Aggregation options.

Supports most standard ES syntax with a few extras:

Example: `es:logstash _type:info_log | agg:terms ip_addr`


### Sort ###

```
sort FIELD,ORD ...
```

Returns the results, sorted.

`FIELD`: The field name to sort on.
`ORD`: `asc` or `desc`.

Example: `sort request_time,asc`


### Load ###

```
load AAA
```
```
load AAA,BBB,...
```

Load a result set.

`AAA`,`BBB`: Variables.
