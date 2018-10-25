<?php

    class PDCCorpusUtility
    {
        public static function SetSessionSavePath()
        {
            //テスト環境と実環境(外部サーバ)でパスが異なるので、
            //ディレクトリの存在確認で場合分けする。
            if(file_exists('/virtual/cjhonyaku/session'))
            {
                session_save_path('/virtual/cjhonyaku/session');
            }
            else if(file_exists('C:\xampp\htdocs\corpus\session'))
            {
                session_save_path('C:/xampp\htdocs\corpus\session');		
            }
            else if(file_exists('C:\MAMP\htdocs\corpus_chinese\session'))
            {
                session_save_path('C:\MAMP\htdocs\corpus_chinese\session');
            }
        }
    }
?>