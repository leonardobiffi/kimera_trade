--view Historico ---
create view view_historico as
select 	date(data_realizacao) as data_realizacao, 
		round(sum(valor_lucro),2) as valor_lucro, 
		sum(case when valor_lucro>0 then 1 else 0 end) as win,
		sum(case when valor_lucro<0 then 1 else 0 end) as loss
from 	app_operacao
GROUP by date(data_realizacao);