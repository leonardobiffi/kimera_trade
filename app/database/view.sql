--view Historico ---
create view view_historico as
select date(data_realizacao) as data_realizacao, round(sum(valor_lucro),2) as valor_lucro
from app_operacao
GROUP by date(data_realizacao);