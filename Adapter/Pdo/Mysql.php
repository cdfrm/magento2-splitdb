<?php

namespace CodeFarm\SplitDb\Adapter\Pdo;

use Magento\Framework\DB\Adapter\Pdo\Mysql as OriginalMysqlPdo;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils;

class Mysql extends OriginalMysqlPdo implements AdapterInterface
{
    public function __construct(
        StringUtils $string,
        DateTime $dateTime,
        LoggerInterface $logger,
        SelectFactory $selectFactory,
        array $config = [],
        SerializerInterface $serializer = null
    ) {

        // only master instance for exclude area
        if(!isset($config['excluded_areas'])){
            $this->excludedAreas = [
                '/checkout',
                '/customer',
            ];
        }else{
            $this->excludedAreas = $config['excluded_areas'];
            unset($config['excluded_areas']);
        }

        if(isset($config['slaves']) && isset($config['is_split'])){
            // keep the same slave throughout the request
            $slaveIndex = rand(0, (count($config['slaves']) - 1));
            $slaveConfig = $config['slaves'][$slaveIndex];
            unset($config['slaves']);
            if($config['is_split']){
                $slaveConfig = array_merge(
                    $config,
                    $slaveConfig
                );
            }else{
                $slaveConfig = $config;
            }

            $this->readConnection = ObjectManager::getInstance()->create(CloneMysql::class, [
                'string' => $string,
                'dateTime' => $dateTime,
                'logger' => $logger,
                'selectFactory' => $selectFactory,
                'config' => $slaveConfig,
                'serializer' => $serializer,
            ]);
        }else{
            $this->readConnection = ObjectManager::getInstance()->create(CloneMysql::class, [
                'string' => $string,
                'dateTime' => $dateTime,
                'logger' => $logger,
                'selectFactory' => $selectFactory,
                'config' => $config,
                'serializer' => $serializer,
            ]);
        }

        parent::__construct(
            $string,
            $dateTime,
            $logger,
            $selectFactory,
            $config,
            $serializer
        );
    }

    /**
     * Check if query is readonly
     */
    protected function canUseReader($sql)
    {
        // for certain circumstances we want to for using the writer
        if(php_sapi_name() == 'cli'){
            return false;
        }

        // allow specific areas to be blocked off
        if(isset($_SERVER['REQUEST_URI'])){
            foreach($this->excludedAreas as $writerOnlyArea){
                if(stripos($_SERVER['REQUEST_URI'], $writerOnlyArea) !== false){
                    return false;
                }
            }
        }

        $writerSqlIdentifiers = [
            'INSERT ',
            'UPDATE ',
            'DELETE ',
            'DROP ',
            'CREATE ',
            'search_tmp',
            'GET_LOCK'
        ];
        foreach($writerSqlIdentifiers as $writerSqlIdentifier){
            if(stripos(substr($sql, 0 , 20), $writerSqlIdentifier) !== false){
                return false;
            }
        }

        return true;
    }

    public function multiQuery($sql, $bind = [])
    {
        if($this->canUseReader($sql)){
            return $this->readConnection->multiQuery($sql, $bind);
        }
        return parent::multiQuery($sql, $bind);
    }

    public function query($sql, $bind = [])
    {
        if($this->canUseReader($sql)){
            return $this->readConnection->query($sql, $bind);
        }
        return parent::query($sql, $bind);
    }
}
