<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

//use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Query\Expr;
use Cdf\BiCoreBundle\Utils\FieldType\FieldTypeUtils;

class ParametriQueryTabellaDecoder extends BaseParametriQueryTabellaDecoder
{
    protected function buildQuery()
    {
        switch ($this->fieldoperator) {
            case Comparison::EQ:
                $this->setEqCriteria();
                break;
            case Comparison::NEQ:
                $this->setNeqCriteria();
                break;
            case Comparison::IN:
                $this->setInCriteria();
                break;
            case Comparison::NIN:
                $this->setNinCriteria();
                break;
            case Comparison::CONTAINS:
                $this->setContainsCriteria();
                break;
            case Comparison::STARTS_WITH:
                $this->setStartswithCriteria();
                break;
            case Comparison::ENDS_WITH:
                $this->setEndswithCriteria();
                break;
            default:
                $this->criteria = null;
                break;
        }
    }

    protected function setEqCriteria()
    {
        $expr = new Expr();

        if (null === $this->fieldvalue) {
            $this->criteria = $expr->isnull($this->fieldname);
        } else {
            if (is_a(FieldTypeUtils::extractDateTime($this->fieldvalue), "\DateTime")) {
                $this->criteria = $expr->eq($this->fieldname, ':'.$this->fieldqueryparameter);
            } else {
                if (is_string($this->fieldvalue)) {
                    $this->criteria = $expr->eq('lower('.$this->fieldname.')', 'lower(:'.$this->fieldqueryparameter.')');
                } else {
                    $this->criteria = $expr->eq($this->fieldname, ':'.$this->fieldqueryparameter);
                }
            }
            $this->parameters = array($this->fieldqueryparameter => $this->fieldvalue);
        }
    }

    protected function setNeqCriteria()
    {
        $expr = new Expr();

        if (null === $this->fieldvalue) {
            $this->criteria = $expr->isnotnull($this->fieldname);
        } else {
            if (is_a(FieldTypeUtils::extractDateTime($this->fieldvalue), "\DateTime")) {
                $this->criteria = $expr->neq($this->fieldname, ':'.$this->fieldqueryparameter);
            } else {
                if (is_string($this->fieldvalue)) {
                    $this->criteria = $expr->neq('lower('.$this->fieldname.')', 'lower(:'.$this->fieldqueryparameter.')');
                } else {
                    $this->criteria = $expr->neq($this->fieldname, ':'.$this->fieldqueryparameter);
                }
            }
            $this->parameters = array($this->fieldqueryparameter => $this->fieldvalue);
        }
    }

    protected function setNinCriteria()
    {
        $expr = new Expr();

        if (is_string($this->fieldvalue)) {
            $this->criteria = $expr->notin('lower('.$this->fieldname.')', 'lower(:'.$this->fieldqueryparameter.')');
        } else {
            $this->criteria = $expr->notin($this->fieldname, ':'.$this->fieldqueryparameter);
        }
        $this->parameters = array($this->fieldqueryparameter => $this->fieldvalue);
    }

    protected function setInCriteria()
    {
        $expr = new Expr();

        if (is_string($this->fieldvalue)) {
            $this->criteria = $expr->in('lower('.$this->fieldname.')', 'lower(:'.$this->fieldqueryparameter.')');
        } else {
            $this->criteria = $expr->in($this->fieldname, ':'.$this->fieldqueryparameter);
        }
        $this->parameters = array($this->fieldqueryparameter => $this->fieldvalue);
    }

    protected function setContainsCriteria()
    {
        $expr = new Expr();
        if (is_string($this->fieldvalue)) {
            $this->criteria = $expr->like('lower('.$this->fieldname.')', 'lower(:'.$this->fieldqueryparameter.')');
        } else {
            $this->criteria = $expr->like($this->fieldname, ':'.$this->fieldqueryparameter);
        }
        $this->parameters = array($this->fieldqueryparameter => '%'.$this->fieldvalue.'%');
    }

    protected function setStartswithCriteria()
    {
        $expr = new Expr();
        if (is_string($this->fieldvalue)) {
            $this->criteria = $expr->like('lower('.$this->fieldname.')', 'lower(:'.$this->fieldqueryparameter.')');
        } else {
            $this->criteria = $expr->like($this->fieldname, ':'.$this->fieldqueryparameter);
        }
        $this->parameters = array($this->fieldqueryparameter => $this->fieldvalue.'%');
    }

    protected function setEndswithCriteria()
    {
        $expr = new Expr();
        if (is_string($this->fieldvalue)) {
            $this->criteria = $expr->like('lower('.$this->fieldname.')', 'lower(:'.$this->fieldqueryparameter.')');
        } else {
            $this->criteria = $expr->like($this->fieldname, ':'.$this->fieldqueryparameter);
        }
        $this->parameters = array($this->fieldqueryparameter => '%'.$this->fieldvalue);
    }
}
