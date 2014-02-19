<?php

// because we need to 
function sanitizeFileName($unclean) {
  $chars_to_clean = array(" ", '"', "'", "$", "&", "\\", "?", "#", "..");
  $replaced_with = '';
  return str_replace($chars_to_clean, $replaced_with, $unclean);
}

