<?php

namespace App;

use File;
use Config;

 class GetMessage
{
    /**
     * ファイルからメッセージ一覧を取る
     * @access public
     * @return arrayメッセージ
     */
    public static function getListMessage()
    {
        $filename = storage_path(\config("getfile.FILE_MESSAGE"));
        $contents = File::get($filename);
        $listMsg = array();
        $contents = explode("\n", $contents);
        foreach ($contents as $line) {
            $listMsg[] = explode(":", $line);
        }
        return $listMsg;
    }
    /**
     * IDでメッセージを取る
     * @access public
     * @param $id
     * @return テキスト
     */
    public static function getMessageByID(string $id){
        $listMsg = GetMessage::getListMessage();
        foreach($listMsg as $msg){
            if(($msg[0] == $id) || ($id == "error001")){
                return trim($msg[1]);
            }
        }
    }
}
