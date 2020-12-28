<?php defined('MW_PATH') || exit('No direct script access allowed');

interface iGateway {
    public function issue();
    public function status();
    public function seal();
}