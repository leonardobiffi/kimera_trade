<?php
/**
 * HistoricoList
 *
 * @version    1.0
 * @package    control
 * @subpackage trade
 * @author     Leonardo Biffi
 */

class HistoricoList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('database');            // defines the database
        parent::setActiveRecord('ViewHistorico');   // defines the active record
        parent::setDefaultOrder('data_realizacao', 'asc');         // defines the default order
        parent::addFilterField('data_realizacao', '>=', 'data_inicio'); // filterField, operator, formField
        parent::addFilterField('data_realizacao', '<=', 'data_final'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_ViewHistorico');
        $this->form->setFormTitle('Histórico');
        
        // create the form fields
        $data_inicio = new TDate('data_inicio');
        $data_final = new TDate('data_final');
        
        // add the fields
        $this->form->addFields( [new TLabel('Início')], [$data_inicio] );
        $this->form->addFields( [new TLabel('Final')], [$data_final] );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ViewHistorico_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        $column_data = new TDataGridColumn('data_realizacao', 'Data', 'left');
        $column_valor = new TDataGridColumn('valor_lucro', 'Valor', 'left');
        $column_win = new TDataGridColumn('win', 'WIN', 'center');
        $column_loss = new TDataGridColumn('loss', 'LOSS', 'center');

        // transformer
        $column_valor->setTransformer(array($this, 'formatValue'));
        $column_data->setTransformer(array($this, 'formatDate'));

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_data);
        $this->datagrid->addColumn($column_win);
        $this->datagrid->addColumn($column_loss); 
        $this->datagrid->addColumn($column_valor);

        $column_valor->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });

        // creates the datagrid column actions     
        $order_data = new TAction(array($this, 'onReload'));
        $order_data->setParameter('order', 'data_realizacao');
        $column_data->setAction($order_data);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
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
     * Format the date according to the country
     */
    public function formatDate($column_date, $object)
    {
        $date = new DateTime($column_date);
        return $date->format('d/m/Y');
    }
}
