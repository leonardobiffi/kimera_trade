<?php
/**
 * ViewHistorico
 *
 * @version    1.0
 * @package    model
 * @subpackage trade
 * @author     Leonardo Biffi
 */

class ViewHistorico extends TRecord
{
    const TABLENAME  = 'view_historico';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'max'; // {max, serial}
}

?>
