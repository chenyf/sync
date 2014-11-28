<?php
/**
 * Created by PhpStorm.
 * 由于在访问之前session都已经存在所以这里不会创建session，只会做session的update和相关操作的记录。
 * User: wanghao1
 * Date: 14-6-30
 * Time: 下午5:51
 */
namespace LETV\Session;
class PDOSessionOperationBackend{

    static $updateMethods = array('PUT', 'DELETE', 'COPY');

    public function __construct(\PDO $pdo, $tableName = 'SyncSession') {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
	\LETV\CLog\CLog::notice("----construct--session---pdo--");
    }


    public function saveOperationIfNeeded($sessionID, $appId, $method){
        $sql = 'SELECT id, uid, deviceId, lastUpdateTime FROM '.$this->tableName.' WHERE sessionCode = ? AND active = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($sessionID, 'Y'));
        $result = $stmt->fetchAll();
	if (!count($result)) return false;

        $userID = $result[0]['uid'];
        $deviceID = $result[0]['deviceId'];
        $lastUpdateTime = $result[0]['lastUpdateTime'];
        //if this is operation involved data update.
        if(in_array($method, self::$updateMethods)){
            //check same operation exist.
	    \LETV\CLog\CLog::notice("--synsession--method-".$method);
            if(! $this->checkIfOperationExist($sessionID, $appId)){
		\LETV\CLog\CLog::notice("---synsession--notexist--sessionID[".$sessionID."] appid[" . $appId . "]");
                $this->saveOperation($sessionID, $userID, $appId);
            }
            \LETV\CLog\CLog::notice("---synsession--exist--sessionID[".$sessionID."] appid[" . $appId . "]");

        }else {
		\LETV\CLog\CLog::notice("--synsession--method-".$method." not in udpateMethods");
               	
	}

    }

    public function checkIfOperationExist($session, $appId, $update = false){
        $sql = 'SELECT id from SyncRecord WHERE sessionCode = ? AND appId = ?';
        $stmt = $this->pdo->prepare($sql);
        if( !$stmt->execute(array($session, $appId)) ){
            $arr = $stmt->errorInfo();
        }
        $result = $stmt->rowCount();
        return ($result > 0);
    }

    public function saveOperation($session, $userID, $app, $update = false){
        $sql = 'INSERT INTO SyncRecord (sessionCode, uid, appId, createTime) values (?, ?, ?, now())';
        $stmt = $this->pdo->prepare($sql);
	\LETV\CLog\CLog::notice("insert--into synrecord--sessionCode:".$session."--uid:".$userID."--app:".$app);
        if(!$stmt->execute(array($session, $userID, $app))){
            $arr = $stmt->errorInfo();
		\LETV\CLog\CLog::notice("----error---".$stmt->errorInfo());
        }
	\LETV\CLog\CLog::notice("-----insertinto ----success--");
    }

    public function updateSession($session){
        $sql = 'UPDATE '.$this->tableName.' SET lastUpdateTime = now() WHERE sessionCode = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($session));
    }

}
