<?php

class Threads {
  function index($numthread = 0) {
    $this->threads = Framework::Load("http://api.thecolorless.net/posts.json");
  }

  function view($num = 0) {
    echo 'wut?'.$num;
  }
}

?>