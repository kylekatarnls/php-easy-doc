<?php

echo serialize(getallheaders())."\n".file_get_contents('php://input');
