# Phiss: Quick and Dirty CSS with PHP

Phiss lets you write CSS in the most simple manner, directly within
your PHP code.

## Quick install

1. Retrieve `phiss.php` however you want to.
2. Put it on your website.
3. Include it with `include`.

## Basic use

Use this PHP snippet, wherever you want you CSS to be:

```
$s = <<<'EOF'
// Here is where all the fun happens.
EOF;
$p = new Phiss($s);
$p->output();
```

With `$s` properly specified, this will create a `<style>` element
containing CSS. Note that `$s` is a Nowdoc. Of course you can use an
Heredoc, but then you have to pay attention to `$variables`. You can
also use a file, see below.

## Basic example

This illustrates most features:

```
pref = {-moz-,-webkit,}
width = 120
body
  font-:
    family: serif
    size: 12px
  width: ${width}px
  height: `return $$width * 2`
  ${pref}border-radius: 5px
```

It returns:

```
body {
  font-family: serif;
  font-size: 12px;
  width: 120px;
  height: 240px;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  border-radius: 5px;
}
```
## Syntax

Phiss is newline- and indentation-sensitive. That's the only way to
differentiate different declarations.

### Comments
Anything after `//` is removed.

### Variables

A variable is declared as:

```
myvar = whatever
```

and is reused as `$myvar` or `${myvar}`.

### Selectors

An unindented line is understood as the selector part of a CSS rule.
The opening brace is optional, unless the selector contains a `=`, in
which case it is mandatory to avoid confusion with variable
declarations.

### Declarations
An indented line containing a colon is a declaration. Use no
semi-colon at the end. You can specify several properties at once
before the colon. Here's basic example:

```
c = 12px
// This is a comment.
h1
  font-family: serif
  font-size: $c

input[type=button] {
  width height: 120px
```

The output will be:

```
h1 {
  font-family: serif;
  font-size: 12px;
}

input[type=button] {
  width: 120px;
  height: 120px;
}
```

Blank lines are optional. Note the use of `{` in the last rule so the
line is not parsed as a variable declaration. You could add a closing
brace, but it's optional.

### Nested declarations

If a declaration contains nothing after the colon, then the
pseudo-propriety will be added as a prefix to the following ones,
which must begin with at least four spaces. As soon as a declaration
begins with less that four spaces, the prefix is cancelled:

```
body
  font-:
    family: serif
    size: 12px
  width: 80%
```

results in

```
body {
  font-family: serif;
  font-size: 12px;
  width: 80%;
}
```

Only one level of nesting is allowed.

### Brace expansion

Something like `{a,b,c}` will create several lines, each containing
the braces replaced by one of the values. It is useful to make several
declarations at once, e.g.:

```
#mybox
  {-moz-,-webkit-,}border-radius: 5px
```

Result:

```
#mybox {
  -moz-border-radius: 5px
  -webkit-border-radius: 5px
  border-radius: 5px
}
```

An empty element, as shown here, is perfectly valid to replace the
braces with nothing. If the brace notation occurs several times on the
same line, all possible outputs are generated.

Brace expansion is performed _in declarations only_. It is not
performed in selectors (where it will most likely result in invalid
CSS) nor in variables declarations. The latter case is interesting,
though:

```
prefix = {-moz-,-webkit-,}
#mybox
  ${prefix}border-radius: 5px
#myotherbox
  ${prefix}border-radius: 2px
```

with the expected output.

### Process substitution

Anything between backticks is processed as PHP code; the code should
return a string, which will be inserted in its place. The final
semi-colon is optional, it is automatically added anyway.

```
body
  width: `return myfunction()`
```

Between backticks, you must use a double `$` to denote variables.
A single `$` denotes a PHP variable.

```
width = 120
body
  width: ${width}px
  height: `return $$width * $php_variable`px
```

will result in (assuming `$php_variable` is `2`):

```
body {
  width: 120px;
  height: 240px;
}
```

## A few things

### Loading strings 

You don't need to pass a string when creating an object. It can be
loaded later with a method:

```
$p = new Phiss();
$p->load($s);
```

The Phiss object accepts strings (split at newlines), but also arrays.
It is convenient if you want to keep your CSS in an external file:

```
$p = new Phiss(file("myfile"));
```

### How lines are processed

Phiss processes lines one by one (with the slight exception of nested
declarations). Here is what happens when a line is processed:

1. Comments are removed.
2. Process substitution is performed (with variables expanded inside
   the backticks).
3. Variables are expanded.
4. Braces are expanded.

### Comparison with Sass, etc.

There is not much to compare. Phiss is simpler but less powerful. Or
less powerful but simpler.

## TODO

* Allow process substitution that returns multiple lines.
* Allow comments that appear in the final CSS.
* Augment the object with new methods, like adding new material,
  returning the CSS without echoing it, etc.
* Write a Vim syntax file. Anybody for Emacs?
