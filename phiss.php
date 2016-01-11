<?php

class Phiss {

private $vars = [];
private $raw;

# split {{{
# Split and trim.
private function split ($p, $s) {
  return array_map("trim", explode($p, $s));
}
# }}}

function __construct ($s) {
  $this->load($s);
}

# replace {{{
# Replaces $var, $$var, ${var}, $${var} with the variable's value.
# Actually the braces don't need to be matched.
private $varmatch = '/\${?([A-Za-z0-9_]+)}?/';
private $doublevarmatch = '/\$\${?([A-Za-z0-9_]+)}?/';
private function replace ($s, $double = false) {
  return preg_replace_callback(
    $double ? $this->doublevarmatch : $this->varmatch,
    function ($m) {
      if (array_key_exists($m[1], $this->vars)) {
        return $this->vars[$m[1]];
      } else {
        # Should be an error.
        return $m[0];
      }
    },
  $s);
}
# }}}

# expand {{{
# Replaces {a,b,c,...} with as many lines.
function expand ($s) {
  $a = array();
  if (preg_match("/{(.*?)}/", $s, $r)) {
    $v = explode(",", $r[1]);
    $c = 1;
    foreach ($v as $w) {
      $a = array_merge($a, $this->expand(preg_replace("/{.*?}/", $w, $s, $c)));
    }
  } else {
    array_push($a, $s);
  }
  return $a;
}
# }}}

# doprint {{{
private function doprint ($s) {
  echo join("\n", $this->expand($this->replace($s))) . "\n";
}
# }}}

# load {{{
function load ($s) {
  if (gettype($s) == "string")
    $s = explode("\n", $s);
  $this->raw = $s;
}
# }}}

function output () {
  echo "\n\n<style>\n";
  $inrule = false;

  foreach ($this->raw as $l) {
    
    # Remove comments.
    $l = preg_replace('+//.*+', "", $l);
    # Code substitution.
    $l = preg_replace_callback('/`(.*?)`/', function ($m) {
      return eval($this->replace($m[1], true) . ";");
    }, $l);

    # Variable.
    if (preg_match("/=/", $l) && !preg_match("/{\s*$/", $l)) {
      if ($inrule) echo "}\n";
      $inrule = false;
      $current = "";
      $v = $this->split("=", $l);
      $this->vars[$v[0]] = $this->replace($v[1]);

    # Selector.
    } elseif (preg_match("/^[^}\s]/", $l)) {
      if ($inrule) echo "}\n";
      $inrule = false;
      $current = "";
      $this->doprint(trim(preg_replace("/\s*{\s*/", "", $l)) . " {", $this->vars);
      $inrule = true;

    # Declaration.
    } elseif (preg_match("/\s{2,}.*:/", $l)) {
      if ($inrule) {
        $v = $this->split(":", $l);
        if ($v[1]) {
          if (!preg_match("/^\s{4,}/", $l)) $current = "";
          $v[0] = $this->split(" ", $v[0]);
          foreach ($v[0] as $d) {
            $this->doprint("  $current$d: $v[1];", $this->vars);
          }
        } else {
          $current = $v[0];
        }
      } else {
        # Should be an error.
      }

    # Blank line.
    } else {
#      if ($inrule) echo "}\n";
#      $inrule = false;
#      $current = "";
      echo "\n";
    }
  }
  if ($inrule) echo "}\n";
  echo "</style>\n";
}

}

?>
