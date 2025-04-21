<?php




/**
 * 
 */
class glob_settings extends glob_dbaseTablePrimary {

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string|null
     */
    public $value;

    /**
     * @var string|null
     */
    public $timeEdited;




    /**
     * @global PDO $pdo
     * @param string $settingName
     * @param mixed $settingValue
     * @param boolean $serialize
     * @return void
     */
    public static function db_setItem( $settingName, $settingValue, $serialize = false ) {

        global $pdo;

        if ( $serialize === true ) {

            $settingValue = json_encode( $settingValue );

        }

        $query = 'SELECT * FROM ' . __CLASS__ . ' WHERE `name` = ?';

        $stmt = $pdo->prepare( $query );

        $stmt->execute([ $settingName ]);

        $raw = $stmt->fetchAll( PDO::FETCH_ASSOC );

        $timeEdited = ( new DateTime() )->format( 'Y-m-d H:i:s' );

        if ( count( $raw ) === 1 ) {

            $query = 'UPDATE ' . __CLASS__ . ' SET `value` = ?, `timeEdited` = ? WHERE `id` = ?';

            $stmt = $pdo->prepare( $query );

            $stmt->execute([ $settingValue, $timeEdited, $raw[ 0 ][ 'id' ] ]);

        } else {

            $query = 'INSERT INTO ' . __CLASS__ . ' ( `id`, `name`, `value`, `timeEdited` ) VALUES ( ?, ?, ?, ? )';

            $stmt = $pdo->prepare( $query );

            $stmt->execute([ null, $settingName, $settingValue, $timeEdited ]);

        }

    }

    /**
     * @global PDO $pdo
     * @param string $settingName
     * @param boolean $unserialize
     * @return string|null
     */
    public static function db_getItem( $settingName, $unserialize = false ) {

        global $pdo;

        $query = 'SELECT * FROM ' . __CLASS__ . ' WHERE `name` = ?';

        $stmt = $pdo->prepare( $query );

        $stmt->execute([ $settingName ]);

        $raw = $stmt->fetchAll( PDO::FETCH_ASSOC );

        if ( count( $raw ) === 1 ) {

            if ( $unserialize === true ) {

                return json_decode( $raw[ 0 ][ 'value' ], true );

            }

            return $raw[ 0 ][ 'value' ];

        } else {

            return null;

        }

    }

    /**
     * @return glob_settings
     */
    public static function db_getLastModified() {

        global $pdo;

        $latest = self::db_getAll( 1, 'timeEdited', 'DESC' );

        return $latest[ 0 ];

    }

    /**
     * @param array $exclude
     * @return array
     */
    public static function db_getAllItems( $exclude = [] ) {

        $toRet = [];

        $settings = self::db_getAll();

        foreach( $settings as $setting ) {

            if ( in_array( $setting->name, $exclude ) ) {

                continue;

            }

            $toRet[ $setting->name ] = $setting->value;

        }

        return $toRet;

    }




    /**
     * @return glob_settings
     */
    public function __construct() {

        return $this;

    }

}