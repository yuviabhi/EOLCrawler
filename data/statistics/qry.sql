/*
For PROVIDER ID : 1280
Total Available Pages : 89033
Pages with Initial details atleast : 89008
Pages with no details at all : 25
Pages with dataobjects : 21067
Pages with no dataobjects : 67941

*/
-- TOTAL AVAILABLE PAGE IDS
select distinct(page_id)
from pages 
where provider_id = 1280

-- PAGES WITH INIT DETAILS ATLEAST
select distinct(page_id)
from pages_details  
where provider_id = 1280

-- PAGES WITH NO DETAILS AT ALL
(
select distinct(page_id)
from pages 
where provider_id = 1280
)
EXCEPT
(
select distinct(page_id)
from pages_details  
where provider_id = 1280
)


-- PAGES WITH DATAOBJS
select distinct(ob.page_id)
from pages_dataobjects ob 
where ob.provider_id = 1280 
order by ob.page_id

-- PAGES WITH NO DATAOBJS
(
select distinct(pd.page_id)
from pages_details pd 
where pd.provider_id = 1280 
order by pd.page_id
)
EXCEPT
(
select distinct(ob.page_id)
from pages_dataobjects ob 
where ob.provider_id = 1280 
order by ob.page_id
)

-- PAGE ID AND DATAOBJ COUNTS
select obj.page_id , count(obj.page_id) as dataobj_count
from pages_dataobjects obj
where obj.provider_id = 1280
group by obj.page_id
order by dataobj_count desc, page_id asc


-- --------------
-- MANUAL CHECKS
-- --------------


-- CHECKING WHETHER DATAOBJ EXIST IN A SPECIFIED PROVIDER ID
create view pages_details_860 as SELECT *
from pages_details 
where provider_id =860
order by provider_id

select * from pages_details_860

select * 
from pages_dataobjects 
where page_id =45518042
order by provider_id



-- CHECK WHERE DATAOBJ EXIST AND DATASUBTYPE =='MAP' Only
(select obj.page_id , count(obj.page_id) as dataobj_count
from pages_dataobjects obj
where obj.provider_id = 1280 
group by obj.page_id
order by dataobj_count desc, page_id asc)
INTERSECT
(select obj.page_id , count(obj.page_id) as map_count
from pages_dataobjects obj
where obj.provider_id = 1280 and obj.datasubtype like '%Map%'
group by obj.page_id
order by map_count desc, page_id asc)



-- CHECK WHERE DATAOBJ EXIST AND DATASUBTYPE !='MAP'
(select obj.page_id , count(obj.page_id) as dataobj_count
from pages_dataobjects obj
where obj.provider_id = 1280 
group by obj.page_id
order by dataobj_count desc, page_id asc)
EXCEPT
(select obj.page_id , count(obj.page_id) as map_count
from pages_dataobjects obj
where obj.provider_id = 1280 and obj.datasubtype like '%Map%'
group by obj.page_id
order by map_count desc, page_id asc)

-- TAXONRANK COUNTS WHERE DATAOBJ EXIST AND DATASUBTYPE !='MAP'
select (((json_array_elements_text(replace(taxonconcepts,chr(26),' ')::json))::json)->'taxonRank')::text as taxonRank , count(distinct(page_id)) as count_page_id
from pages_details 
where provider_id = 1280 and page_id in
			(select page_id from (
				(select obj.page_id , count(obj.page_id) as dataobj_count
				from pages_dataobjects obj
				where obj.provider_id = 1280 
				group by obj.page_id
				order by dataobj_count desc, page_id asc)
				EXCEPT
				(select obj.page_id , count(obj.page_id) as map_count
				from pages_dataobjects obj
				where obj.provider_id = 1280 and obj.datasubtype like '%Map%'
				group by obj.page_id
				order by map_count desc, page_id asc)
				) as temp
			)
group by taxonRank
order by count_page_id desc, taxonRank

-- TAXONRANK AND PAGE IDS WHERE DATAOBJ EXIST AND DATASUBTYPE !='MAP'
select * from (
select distinct(page_id) , ((((json_array_elements_text(replace(taxonconcepts,chr(26),'')::json))::json)->'taxonRank')::text) as  taxonrank
from pages_details 
where provider_id = 1280 
			and page_id in
			(select page_id from (
				(select obj.page_id , count(obj.page_id) as dataobj_count
				from pages_dataobjects obj
				where obj.provider_id = 1280 
				group by obj.page_id
				order by dataobj_count desc, page_id asc)
				EXCEPT
				(select obj.page_id , count(obj.page_id) as map_count
				from pages_dataobjects obj
				where obj.provider_id = 1280 and obj.datasubtype like '%Map%'
				group by obj.page_id
				order by map_count desc, page_id asc)
				) as temp
			)


) temp 
-- where taxonrank like "%Species%" 
order by taxonrank



