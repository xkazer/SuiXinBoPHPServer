<?php
/**
 * 创建房间接口
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/AvRoom.php';
require_once MODEL_PATH . '/InteractAvRoom.php';
require_once MODEL_PATH . '/Account.php';

class CreateLiveRoomCmd extends TokenCmd
{

    private $avRoom;

    public function parseInput()
    {
        if (!isset($this->req['type']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of type');
        }
        if (!is_string($this->req['type']))
        {
             return new CmdResp(ERR_REQ_DATA, ' Invalid type');
        }
        
        $this->avRoom = new AvRoom($this->user);
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $ret = $this->avRoom->load();
        // 加载房间出错
        if ($ret < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error'); 
        }

        //房间不存在，执行创建
        if($ret == 0)
        {
            $ret = $this->avRoom->create();
            if (!$ret)
            {
                return new CmdResp(ERR_SERVER, 'Server internal error: create av room fail'); 
            }
        } 

        //房间id
        $id = $this->avRoom->getId();
        //房间成员设置
        $interactAvRoom = new InteractAvRoom($this->user, $id, 'off', 1);
        //主播加入房间列表
        $ret = $interactAvRoom->enterRoom();    
        if(!$ret)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error:insert record into interactroom fail'); 
        }

        return new CmdResp(ERR_SUCCESS, '', array('roomnum' => (int)$id, 'groupid' => (string)$id));
    }    
}
