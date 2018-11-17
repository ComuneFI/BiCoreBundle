<?php
namespace Cdf\BiCoreBundle\Utils\Arrays;

class ArrayUtils
{
    /**
     * La funzione cerca un valore $elem nell'array multidimensionale $array all'interno di ogni elemento con chiave $key di ogni riga di array
     * e restituisce l'indice.
     *
     * @param $elem mixed Elemento da cercare nell'array
     * @param $array Array nel quale cercare
     * @param $key string Nome della chiave nella quale cercare $elem
     *
     * @return mixed False se non trovato l'elemento, altrimenti l'indice in cui si è trovato il valore
     */
    public static function inMultiarray($elem, $array, $key)
    {
        foreach ($array as $indice => $value) {
            if (!is_array($value)) {
                return false;
            }
            if (array_key_exists($key, $value)) {
                foreach ($value as $nomecolonna => $colonna) {
                    if ($colonna === $elem && $nomecolonna == $key) {
                        return $indice;
                    }
                }
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * La funzione cerca un valore $elem nell'array multidimensionale $array all'interno di ogni elemento con chiave $key di ogni riga di array
     * e restituisce l'indice
     *
     * @param $elem mixed Elemento da cercare nell'array
     * @param $array Array nel quale cercare
     * @param $key mixed Nome della chiave nella quale cercare $elem
     * @return Mixed False se non trovato l'elemento, altrimenti il vettore con tutti gli indici
     */
    public static function inMultiarrayTutti($elem, $array, $key)
    {

        $trovato = array();

        foreach ($array as $indice => $value) {
            if (!is_array($value)) {
                return false;
            }
            if (array_key_exists($key, $value)) {
                foreach ($value as $nomecolonna => $colonna) {
                    if ($colonna === $elem && $nomecolonna == $key) {
                        $trovato[] = $indice;
                    }
                }
            } else {
                return false;
            }
        }
        return (count($trovato) > 0 ? $trovato : false);
    }

    /**
     * La funzione cerca un valore $elem nell'array multidimensionale $array all'interno di ogni elemento con chiave $key di ogni riga di array
     * e restituisce l'indice
     *
     * @param $array Array nel quale cercare
     * @param $search array Chiave-valore da cercare
     * @return Mixed False se non trovato l'elemento, altrimenti l'indice in cui si trova il valore
     */
    public static function multiInMultiarray($array, $search, $debug = false, $tutti = false)
    {
        $primo = true;
        $vettorerisultati = array();

        foreach ($search as $key => $singolaricerca) {
            $trovato = self::inMultiarrayTutti($singolaricerca, $array, $key, $debug);

            if ($trovato === false) {
                $vettorerisultati = false;
                break;
            }

            if ($primo) {
                $vettorerisultati = $trovato;
            } else {
                $vettorerisultati = array_intersect($vettorerisultati, $trovato);
            }

            $primo = false;
        }

        if ($vettorerisultati === false) {
            $risposta = false;
        } elseif ($tutti === false) {
            $risposta = reset($vettorerisultati);
        } else {
            $risposta = $vettorerisultati;
        }

        return $risposta;
    }

    /**
     * La funzione ordina un array multidimensionale $array.
     *
     * @param $array Array da ordinare
     * @param $key string Nome della chiave dell'array per cui ordinare
     * @param $type int Tipo di ordinamento SORT_ASC, SORT_DESC
     *
     * @return array Ritorna l'array ordinato
     *
     * @example arrayOrderby($rubrica,"cognome",SORT_ASC);
     *          <br/>$rubrica = array();<br/>
     *          $rubrica[] = array("matricola" => 99999, "cognome" => "rossi", "nome" => "mario");<br/>
     *          $rubrica[] = array("matricola" => 99998, "cognome" => "bianchi", "nome" => "andrea");
     *          $rubrica[] = array("matricola" => 99997, "cognome" => "verdi", "nome" => "michele");
     *          rusulterà<br/>$rubrica[0]("matricola"=>99998,"cognome"=>"bianchi","nome"=>"andrea")
     *          $rubrica[1]("matricola"=>99999,"cognome"=>"rossi","nome"=>"mario")
     *          $rubrica[2]("matricola"=>99997,"cognome"=>"verdi","nome"=>"michele")
     */
    public static function arrayOrderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    public function arraySearchRecursive($needle, $haystack)
    {
        foreach ($haystack as $key => $val) {
            if (stripos(implode('', $val), $needle) > 0) {
                return $key;
            }
        }

        return false;
    }

    /**
     * La funzione ordina un array multidimensionale  che ha per indice chiavi associative.
     *
     * @param $array Array da ordinare
     * @param $subkey string Nome della chiave dell'array associato alla chiave per cui ordinare
     * @param $sort_ascending boolean Tipo di ordinamento true ASC, false DESC
     *
     * @return array Ritorna l'array ordinato
     *
     * @example sortMultiAssociativeArray($rubrica, "ordine", true);<code>
     *          array["nominativo" => ["nomecampo" => "nominativo","ordine" => 30],<br/><br/>
     *          "datanascita" => ["nomecampo" => "datanascita","ordine" => 10],<br/><br/>
     *          "telefono" => ["nomecampo" => "telefono","ordine" => 20]<br/><br/>
     *          ritorna:<br/><br/>
     *          array["datanascita" => ["nomecampo" => "datanascita","ordine" => 10],<br/><br/>
     *          "telefono" => ["nomecampo" => "telefono","ordine" => 20],<br/><br/>
     *          "nominativo" => ["nomecampo" => "nominativo","ordine" => 30]</code>
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public static function sortMultiAssociativeArray(&$array, $subkey, $sort_ascending = true)
    {
        $check = null;
        $diff = false;
        //Controlla se sono tutti uguali i valori per i quali deve fare l'ordinamento
        foreach ($array as $key => $val) {
            if (isset($check) && $check != $val[$subkey]) {
                $diff = true;
                break;
            } else {
                $check = $val[$subkey];
            }
        }
        //Se sono tutti uguali (stesso "peso") evita di fare l'ordinamento
        if ($diff) {
            if (count($array)) {
                $temp_array[key($array)] = array_shift($array);
            }

            foreach ($array as $key => $val) {
                $offset = 0;
                $found = false;
                foreach ($temp_array as $tmp_key => $tmp_val) {
                    if (!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                        $temp_array = array_merge(
                            (array) array_slice($temp_array, 0, $offset),
                            array($key => $val),
                            array_slice($temp_array, $offset)
                        );
                        $found = true;
                    }
                    $offset++;
                }
                if (!$found) {
                    $temp_array = array_merge($temp_array, array($key => $val));
                }
            }
            if ($sort_ascending) {
                $array = array_reverse($temp_array);
            } else {
                $array = $temp_array;
            }
        }
    }
}
