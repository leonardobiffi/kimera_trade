<?php
/**
 * OperacaoFormView
 *
 * @version    1.0
 * @package    control
 * @subpackage trade
 * @author     Leonardo Biffi
 */

class OperacaoFormView extends TPage
{
    private $form;
    private $datagrid;
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the items form and add a table inside
        $this->form = new BootstrapFormBuilder('form_operacao');
        $this->form->setFormTitle('Operações');
        
        // create the form fields
        $id_parmoeda            = new TDBSeekButton('id_parmoeda', 'database', 'form_operacao', 'ParMoeda', 'descricao');
        $parmoeda_descricao     = new TEntry('parmoeda_descricao');
        $payout                 = new TEntry('payout');
        $valor_entrada          = new TEntry('valor_entrada');
        $valor_total            = new TEntry('valor_total');
        
        // add validators
        $id_parmoeda->addValidation('Par Moeda', new TRequiredValidator);

        $valor_entrada->setExitAction(new TAction(array($this, 'onUpdateFinal')));
        $payout->setExitAction(new TAction(array($this, 'onUpdateFinal')));
        $id_parmoeda->setExitAction(new TAction(array($this, 'onUpdateFinal')));
        
        // define some attributes
        $id_parmoeda->style = 'font-size: 17pt';
        $parmoeda_descricao->style = 'font-size: 17pt';
        $payout->style = 'font-size: 17pt';
        $valor_entrada->style = 'font-size: 17pt';
        $valor_total->style = 'font-size: 17pt';
        $id_parmoeda->button->style = 'margin-top:0px; vertical-align:top';
        
        // define some properties
        $id_parmoeda->setSize(50);
        $id_parmoeda->setAuxiliar($parmoeda_descricao);
        $parmoeda_descricao->setEditable(FALSE);
        $valor_total->setEditable(FALSE);
        $parmoeda_descricao->setSize(150);
        $valor_entrada->setNumericMask(2, ',', '.');
        $valor_total->setNumericMask(2, ',', '.');
        $valor_entrada->setSize(225);
        
        // create the field labels
        $lab_pro = new TLabel('Par Moeda');
        $lab_pri = new TLabel('Payout %');
        $lab_amo = new TLabel('Entrada');
        $lab_tot = new TLabel('Total');
        $lab_pro->setFontSize(17);
        $lab_pri->setFontSize(17);
        $lab_amo->setFontSize(17);
        $lab_tot->setFontSize(17);
        $lab_pro->setFontColor('red');
        $lab_amo->setFontColor('red');
        $this->form->addField($parmoeda_descricao);
        
        // add the form fields
        $this->form->addFields([$lab_pro], [$id_parmoeda], [$lab_pri], [$payout]);
        $this->form->addFields([$lab_amo], [$valor_entrada], [$lab_tot], [$valor_total]);
        $btnWin = $this->form->addAction('WIN&ensp;', new TAction(array($this, 'onSave')),'fa:caret-up');
        $btnLoss = $this->form->addAction('LOSS', new TAction(array($this, 'onSaveLoss')), 'fa:caret-down');
        //$this->form->addAction('Clear', new TAction(array($this, 'onClear')),   'fa:trash red');
        
        $btnWin->class = 'btn btn-success btn-lg';
        $btnLoss->class = 'btn btn-danger btn-lg';
        
        // creates the grid for items
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->makeScrollable();
        $this->datagrid->setHeight( 300 );

        $data_op = $this->datagrid->addQuickColumn('Data', 'data_realizacao', 'left');
        $entrada = $this->datagrid->addQuickColumn('Entrada', 'valor_entrada', 'left');
        $this->datagrid->addQuickColumn('Par Moeda', 'id_parmoeda', 'left');
        $payout = $this->datagrid->addQuickColumn('Payout', 'payout', 'left');
        $total = $this->datagrid->addQuickColumn('Lucro', 'valor_lucro', 'right');
        
        $total->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });
        
        $total->setTransformer(array($this, 'formatValue'));
        $entrada->setTransformer(array($this, 'formatValue'));
        $payout->setTransformer(array($this, 'formatPorc'));
        
        
        $this->datagrid->addQuickAction('Delete', $a3=new TDataGridAction(array($this, 'onDelete')), 'id_parmoeda', 'fa:trash red');
        $this->datagrid->createModel();
        $a3->setParameter('register_state', 'false');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add($panel = TPanelGroup::pack('Operações', $this->datagrid));
        $panel->getBody()->style = 'overflow-x: auto';
        parent::add($vbox);
    }
    
    /**
     * Add a product into the cart
     */
    public function onAddItem()
    {
        try
        {
            $this->form->validate(); // validate form data
            
            $items = TSession::getValue('items'); // get items from session
            $item = $this->form->getData('SaleItem');
            
            $item->sale_price = str_replace(['.', ','], ['', '.'], $item->sale_price);
            $item->total      = str_replace(['.', ','], ['', '.'], $item->total);
            
            $items[ $item->product_id ] = $item; // add the item
            
            TSession::setValue('items', $items); // store back tthe session
            $this->form->clear(); // clear form
            $this->onReload(); // reload data
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Format value
     */
    public function formatValue($stock, $object, $row)
    {
        $number = 'R$ '.number_format($stock, 2, ',', '.');
        if ($stock >= 0)
        {
            return "<span style='color:blue'>$number</span>";
        }
        else
        {
            $row->style = "background: #F6D8CE";
            return "<span style='color:red'>$number</span>";
        }
    }

    /**
     * Format value
     */
    public function formatPorc($stock, $object, $row)
    {
        $number = $stock. '%';

        return $number;
    }
    
    /**
     * Clear items
     */
    public function onClear($param)
    {
        TSession::setValue('items', []);
        $this->form->clear(); // clear form
        $this->onReload(); // reload data
    }
    
    /**
     * Select customer
     */
    public function onCustomer($param)
    {
        $form = new TQuickForm('form_customer');
        $form->style = 'padding:20px';
        
        $customer_id   = new TDBSeekButton('customer_id', 'samples', 'form_customer', 'Customer', 'name', 'customer_id', 'customer_name');
        $customer_name = new TEntry('customer_name');
        $customer_id->setAuxiliar($customer_name);
        $customer_name->setEditable(FALSE);
        
        $form->addQuickField('Customer', $customer_id);
        
        $customer_id->setSize(50);
        $customer_name->setSize(200);
        
        $form->addQuickAction('Save', new TAction(array($this, 'onSave')), 'fa:save green');
        
        // show the input dialog
        new TInputDialog('Customer', $form);
    }
    
    /**
     * Saves the cart
     */
    public function onSave( $param )
    {
        try
        {
            
            $data = (object) $param;
            
            TTransaction::open('database');

            $this->form->validate(); // validate form data

            $data->valor_entrada = str_replace(['.', ','], ['', '.'], $data->valor_entrada);
            $data->valor_final = (($data->valor_entrada * $data->payout) / 100) + $data->valor_entrada;

            $op = new Operacao;
            $op->data_realizacao = date("Y-m-d H:i:s");
            $op->id_parmoeda = $data->id_parmoeda;
            $op->valor_entrada = $data->valor_entrada;
            $op->valor_final = $data->valor_final;
            $op->valor_lucro = $data->valor_final - $data->valor_entrada;
            $op->payout = $data->payout;
            $op->id_usuario = TSession::getValue('userid');

            $op->store();

            
            TTransaction::close();
            $this->onReload();

        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Saves the cart
     */
    public function onSaveLoss( $param )
    {
        try
        {
            
            $data = (object) $param;
            
            TTransaction::open('database');

            $this->form->validate(); // validate form data

            $data->valor_entrada = str_replace(['.', ','], ['', '.'], $data->valor_entrada);
            $data->valor_lucro = $data->valor_entrada * -1;

            $op = new Operacao;
            $op->data_realizacao = date("Y-m-d H:i:s");
            $op->id_parmoeda = $data->id_parmoeda;
            $op->valor_entrada = $data->valor_entrada;
            $op->valor_lucro = $data->valor_lucro;
            $op->payout = $data->payout;
            $op->id_usuario = TSession::getValue('userid');

            $op->store();

            
            TTransaction::close();
            $this->onReload();

        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Exit action for the field product
     * Fill some form fields (sale_price, amount, total)
     */
    public static function onExitProduct($param)
    {
        $product_id = $param['product_id']; // get the product code
        try
        {
            TTransaction::open('samples');
            $product = new Product($product_id); // reads the product
            
            $obj = new StdClass;
            $obj->sale_price  = number_format($product->sale_price, 2, ',', '.');
            $obj->amount = 1;
            $obj->total       = number_format($product->sale_price, 2, ',', '.');
            TTransaction::close();
            TForm::sendData('form_operacao', $obj);
        }
        catch (Exception $e)
        {
            // does nothing
        }
    }
    
    /**
     * Update the total based on the sale price, amount
     */
    public static function onUpdateFinal($param)
    {
        $payout             = $param['payout'];
        $valor_entrada      = $param['valor_entrada'];
        
        $obj = new StdClass;
        $obj->valor_final = number_format( (($valor_entrada * $payout) / 100) + $valor_entrada, 2, ',', '.');
        TForm::sendData('form_operacao', $obj);
    }
    
    /**
     * Remove a product from the cart
     */
    public function onDelete($param)
    {
        // get the cart objects from session
        $items = TSession::getValue('items');
        unset($items[ $param['key'] ]); // remove the product from the array
        TSession::setValue('items', $items); // put the array back to the session
        
        // reload datagrid
        $this->onReload( func_get_arg(0) );
    }
    
    /**
     * Reload the datagrid with the objects from the session
     */
    function onReload($param = NULL)
    {
        try
        {
            $this->datagrid->clear(); // clear datagrid

            TTransaction::open('database');

            // instancia um repositório
            $repository = new TRepository('Operacao');

            $criteria = new TCriteria();
            $criteria->setProperty('order', 'data_realizacao desc');
            $criteria->add(new TFilter("date(data_realizacao)","=",date('Y-m-d')));

            // load the objects according to criteria
            $items = $repository->load($criteria);
            
            if ($items)
            {
                foreach ($items as $object)
                {
                    // add the item inside the datagrid
                    $object->id_parmoeda = ParMoeda::getNome($object->id_parmoeda);

                    $this->datagrid->addItem($object);
                }
            }
            $this->loaded = true;

            $repo = new TRepositorySum('Operacao'); 
            $object = $repo->sum(NULL, array('valor_total' => 'valor_lucro'));
            
            $data = new stdClass;
            $data->valor_total = $object->valor_total;
            
            TForm::sendData('form_operacao', $data);


            TTransaction::close();

        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Show the page
     */
    public function show()
    {
        if (!$this->loaded)
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}