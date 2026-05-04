<?php
echo extension_loaded('gd') ? 'GD: OK' : 'GD: غير مفعّل';
echo '<br>';
var_dump(gd_info());