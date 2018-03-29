<?php
class Cuota_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function getCuotas()
    {
        $this->db->select('id, nombre, mes, dia, monto, rubro_id, (SELECT nombre FROM rubro where id = rubro_id) as nombreRubro');
        $this->db->order_by('id', 'DESC');
        
        $query = $this->db->get('cuota');

        return $query->result();
    }

    public function getCuotasByRubro($id)
    {
        // $this->db->select('id, nombre, mes, dia, monto, rubro_id');
        // $this->db->where('rubro_id', $id);
        // $query = $this->db->get('cuota');


        // $dateValue = strtotime('2012-06-05');
        // $year = date('Y',$dateValue);
        // $monthName = date('F',$dateValue);
        // $monthNo = date('m',$dateValue);
        $t=date('d-m-Y');
        $dayNum = strtolower(date("d",strtotime($t)));
        $monthNum = strtolower(date("m",strtotime($t)));
        $yearNum = date("Y");


        $this->db->select("cuota.id, cuota.nombre, cuota.mes, cuota.dia, cuota.monto, cuota.rubro_id,to_date(rubro.year || '-' || get_month_number(cuota.mes) || '-' || cuota.dia,'yyyy-MM-dd') as cuota_fecha");
        $this->db->from('cuota');
        $this->db->join('rubro', 'rubro.id = cuota.rubro_id');
        $this->db->where('rubro.id', $id);
        //traer solo las cuotas cuya fecha sea mayor a la fecha de hoy
        $this->db->where("to_date(rubro.year || '-' || get_month_number(cuota.mes) || '-' || cuota.dia,'yyyy-MM-dd') >=",date('Y-m-d'));


        $query = $this->db->get();

        return $query->result();

    }

    public function getCuota($id)
    {
        $this->db->select('id, nombre, mes, dia, monto, rubro_id');
        $this->db->where('id', $id);
        $query = $this->db->get('cuota');

        return $query->row();
    }

    public function deleteCuota($id)
    {
        $this->db->where('id', $id);

        $this->db->delete('cuota');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function crearCuota($nombre, $mes, $dia, $monto, $rubro_id)
    {
        $data = array(
            'nombre'    => $nombre,
            'mes'     => $mes,
            'dia'      => $dia,
            'monto'      => $monto,
            'rubro_id'      => $rubro_id
        );

        $this->db->insert('cuota', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        else {
            return false;
        }
    }

    public function updateCuota($nombre, $mes, $dia, $monto, $rubro_id, $id)
    {
        $data = array(
            'nombre'    => $nombre,
            'mes'     => $mes,
            'dia'      => $dia,
            'monto'      => $monto,
            'rubro_id'      => $rubro_id
        );

        $this->db->where('id', $id);
        $this->db->update('cuota', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }
}