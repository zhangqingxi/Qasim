<?php

require "src/Api/Db/Db.php";

require "src/Api/Db/Connection.php";

require "src/Api/Db/Query.php";

require "src/Api/Log.php";

require "src/base.php";

//\Qasim\Db::query('SELECT docid from cx_tags where docid = :docid', [':docid' => 1]);
\Qasim\Db\Db::name('banknum')->where('docid', 1) -> select();