<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
  protected $currentDate;
  protected $pathImage = "assets/image/";
  protected $pathDocument = "assets/file/project/";
  protected $listJoinAll = [
    'first',
    'second',
    'third',
    'fourth',
    'fifth',
    'sixth',
    'seventh',
    'eighth',
    'ninth',
    'tenth'
  ];

  public function __construct()
  {
    $this->currentDate = date("Y-m-d H:i:s");
  }

  protected function setUpPayload($condition, $tableSelf)
  {
    $alias = "selfTable";
    $builder = DB::table($tableSelf . " AS " . $alias);
    if ($condition) {
      if (isset($condition['select'])) {
        foreach ($condition['select'] as $select) {
          $builder = $builder->addSelect($alias . "." . $select);
        }
      } else {
        $builder = $builder->addSelect($alias . ".*");
      }

      if (isset($condition['joins'])) {
        $no = 0;
        foreach ($condition['joins'] as $join) {
          $tableJoin = $join['name1'] ?? $alias;
          $tableName  = $join['name'];
          $columnJoin = $join['column_join'];
          $columnSelf = $join['column_self'] ?? "id";

          if (isset($join['column_results'])) {
            $columnResult = $join['column_results'];

            foreach ($columnResult as $sColumn) {
              $builder = $builder->addSelect($tableName . "." . $sColumn . " as join_" . $this->listJoinAll[$no] . "_" . $sColumn);
            }
          }
          $builder = $builder->leftJoin($tableName, $tableJoin . "." . $columnJoin, '=', $tableName . '.' . $columnSelf);
          $no++;
        }
      }

      if (isset($condition['columns'])) {
        $listWhere = $condition['columns'];

        $builder = $builder->where(function ($query)  use ($listWhere, $alias) {
          foreach ($listWhere as $where) {
            $value = $where['value'];
            if ($value && $value != "" && $value != " ") {
              $column = $where['name'];
              $operator = strtolower($where['logic_operator']); // like, =, <>, range, isnull, notnull
              $value2 = isset($where['value1']) ? $where['value1'] : "";
              $tableColumn = isset($where['table_name']) ? $where['table_name'] : $alias;
              $query = $this->whereCondition($query, $operator, $tableColumn, $column, $value, $value2);
            }
          }
        });
      }

      if (isset($condition['group_column'])) {
        $builder = $this->groupWhere($builder, $condition['group_column'], $alias);
      }

      $data['count'] = clone $builder;
      $data['distinct'] =  clone $builder->distinct();

      if (isset($condition['paging'])) {
        $builder = $builder->offset($condition['paging']['start'])->limit($condition['paging']['length']);
      }

      if (isset($condition['orders'])) {
        $orders = $condition['orders'];
        $sortBy = $orders['ascending'] ? "ASC" : "DESC";
        $columnOrder = $orders['columns'];
        foreach ($columnOrder as $column) {
          $builder = $builder->orderBy($alias . "." . $column, $sortBy);
        }
      }
    }

    $data['builder'] = $builder;

    return $data;
  }

  private function groupWhere($oldBuilder, $groupWhere, $alias)
  {
    $builder = $oldBuilder;
    $listGroupWhere = $groupWhere;
    $operator = strtolower($listGroupWhere['operator']);
    $operatorGroup = strtolower($listGroupWhere['group_operator']);
    $listWhere = $listGroupWhere['where'];
    if ($operator == "and") {
      $builder = $builder->where(function ($query) use ($operatorGroup, $listWhere, $alias) {
        $no = 1;
        foreach ($listWhere as $where) {
          $value = $where['value'];
          $column = $where['name'];
          $operator = strtolower($where['logic_operator']);
          $value2 = isset($where['value1']) ? $where['value1'] : "";
          $tableColumn = isset($where['table_name']) ? $where['table_name'] : $alias;
          if ($value && $value != "" && $value != " ") {
            if ($operatorGroup == "and") {
              $query = $this->whereCondition($query, $operator, $tableColumn, $column, $value, $value2);
            } else {
              if ($no == 1) {
                $query = $this->whereCondition($query, $operator, $tableColumn, $column, $value, $value2);
              } else {
                $query = $this->orWhereCondition($query, $operator, $tableColumn, $column, $value, $value2);
              }
            }
          }
          $no = $no + 1;
        }
      });
    } else if (strtolower($operator) == "or") {
      $builder = $builder->orWhere(function ($query) use ($operatorGroup, $listWhere, $alias) {
        $no = 1;
        foreach ($listWhere as $where) {
          $value = $where['value'];
          $column = $where['name'];
          $operator = strtolower($where['logic_operator']);
          $value2 = isset($where['value1']) ? $where['value1'] : "";
          $tableColumn = isset($where['table_name']) ? $where['table_name'] : $alias;
          if ($value && $value != "" && $value != " ") {
            if ($operatorGroup == "and") {
              $query = $this->whereCondition($query, $operator, $tableColumn, $column, $value, $value2);
            } else {
              if ($no == 1) {
                $query = $this->whereCondition($query, $operator, $tableColumn, $column, $value, $value2);
              } else {
                $query = $this->orWhereCondition($query, $operator, $tableColumn, $column, $value, $value2);
              }
            }
          }
          $no++;
        }
      });
    }
    return $builder;
  }

  private function whereCondition($oldQuery, $operator, $tableColumn, $column, $value, $value2)
  {
    $query = $oldQuery;
    if ($operator == "range") {
      $query = $query->whereBetween($tableColumn . "." . $column, [$value, $value2]);
    } else if ($operator == "like") {
      $query = $query->where($tableColumn . "." . $column, 'like', '%' . $value . '%');
    } else if ($operator == "like_front") {
      $query = $query->where($tableColumn . "." . $column, 'like', $value . '%');
    } else if ($operator == "like_end") {
      $query = $query->where($tableColumn . "." . $column, 'like', '%' . $value);
    } else if ($operator == "=") {
      $query = $query->where($tableColumn . "." . $column, $value);
    } else if ($operator == "in") {
      $query = $query->whereIn($tableColumn . "." . $column, $value);
    } else if ($operator == "notin") {
      $query = $query->whereNotIn($tableColumn . "." . $column, $value);
    } else if ($operator == "isnull") {
      $query = $query->WhereNull($tableColumn . "." . $column);
    } else if ($operator == "notnull") {
      $query = $query->WhereNotNull($tableColumn . "." . $column);
    } else {
      $query = $query->where($tableColumn . "." . $column, $operator, $value);
    }
    return $query;
  }

  private function orWhereCondition($oldQuery, $operator, $tableColumn, $column, $value, $value2)
  {
    $query = $oldQuery;
    if ($operator == "range") {
      $query = $query->orWhereBetween($tableColumn . "." . $column, [$value, $value2]);
    } else if ($operator == "like") {
      $query = $query->orWhere($tableColumn . "." . $column, 'like', '%' . $value . '%');
    } else if ($operator == "like_front") {
      $query = $query->where($tableColumn . "." . $column, 'like', $value . '%');
    } else if ($operator == "like_end") {
      $query = $query->where($tableColumn . "." . $column, 'like', '%' . $value);
    } else if ($operator == "ilike") {
      $query = $query->where($tableColumn . "." . $column, '~*', $value);
    } else if ($operator == "=") {
      $query = $query->orWhere($tableColumn . "." . $column, $value);
    } else if ($operator == "in") {
      $query = $query->orWhereIn($tableColumn . "." . $column, $value);
    } else if ($operator == "notin") {
      $query = $query->orWhereNotIn($tableColumn . "." . $column, $value);
    } else {
      $query = $query->orWhere($tableColumn . "." . $column, $operator, $value);
    }
    return $query;
  }

  public function handleValidationException($request, array $data)
  {
    try {
      $validated = $this->validate($request, $data);
      return $validated;
    } catch (ValidationException $e) {
      $stackErr = array();
      foreach ($e->errors() as $key => $value) {
        $stackErr[$key] = $value;
      }
      return response()->json([
        'status' => 422,
        'errors' =>  $stackErr,
        'message' => 'Error validations',
      ], 422);
    }
  }
}
