<?php

namespace App\Http\Controllers;

use App\Models\Dpt;
use App\Models\Saksi;
use App\Models\Token;
use Illuminate\Http\Request;

class KoordinatorTpsController extends Controller
{

    public function koordinatortps()
    {
        $saksiData = Saksi::all();
        return view('page.Koordinator_Tps.table',compact('saksiData'));
    }

    public function jadikan_koorTps(Request $request)
    {
        $username = $request->username;
        $password = $request->password;
        $passwordHashed = bcrypt($password); // Menyimpan password yang telah di-hash
        $noTlpn = $request->NoTlpn;
        $dpt = Dpt::findOrFail($request->saksiId);
        
        // Pengecekan nomor telepon
        $token = Token::first()->token; // Ganti dengan token yang sesuai
        $phone = $noTlpn;
    
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: $token",
            "url: https://pati.wablas.com",
        ]);
        curl_setopt($curl, CURLOPT_URL,  "https://phone.wablas.com/check-phone-number?phones=". urlencode($phone));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    
        $result = curl_exec($curl);
        curl_close($curl);
    
        $response = json_decode($result, true);

        // dd($result);
    
        if ($response['status'] === 'success' && $response['data'][0]['status'] === 'online') {
            // Nomor valid dan aktif, lanjutkan menyimpan data saksi
            $saksi = new Saksi();
            $saksi->no_kk = $dpt->no_kk;
            $saksi->nik = $dpt->nik;
            $saksi->nama = $dpt->nama;
            $saksi->tempat_lahir = $dpt->tempat_lahir;
            $saksi->tanggal_lahir = $dpt->tanggal_lahir;
            $saksi->status_perkawinan = $dpt->status_perkawinan;
            $saksi->jenis_kelamin = $dpt->jenis_kelamin;
            $saksi->jalan = $dpt->jalan;
            $saksi->rt = $dpt->rt;
            $saksi->rw = $dpt->rw;
            $saksi->disabilitas = $dpt->disabilitas;
            $saksi->kota = $dpt->kota;
            $saksi->kelurahan = $dpt->kelurahan;
            $saksi->kecamatan = $dpt->kecamatan;
            $saksi->tps = $dpt->tps;
 
            // Mengisi kolom username, password, dan NoTlpn

            $saksi->dpt_id = $dpt->id; // Gantilah $dptId dengan ID DPT yang sesuai
            // Memanggil variabel $username, $password, dan $noTlpn
            $saksi->username = $username;
            $saksi->password = $passwordHashed; // Mengisi kolom password dengan password yang telah di-hash
            $saksi->NoTlpn = $phone;
                
        
            $saksi->save();
            
            return redirect()->route('saksi')->with('success', 'Nomor WhatsApp valid.');
        } else {
            // Nomor tidak valid atau tidak aktif, kembali dengan pesan kesalahan
            return redirect()->route('saksi')->with('error','Nomor berikut tidak terdaftar Pada WhatsApp.');
        }
    }

    public function edit_koortps($id)
    {   
        $data = Saksi::findOrFail($id);
        
        return view('page.Koordinator_Tps.edit', compact('data'));
    }

    public function update_koortps(Request $request, $id)
    {
        $data = Saksi::findOrFail($id);

        // Pengecekan nomor telepon
        $token = Token::first()->token; // Ganti dengan token yang sesuai
        $phone = $request->NoTlpn;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: $token",
            "url: https://pati.wablas.com",
        ]);
        curl_setopt($curl, CURLOPT_URL,  "https://phone.wablas.com/check-phone-number?phones=". urlencode($phone));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($result, true);

        if ($response['status'] === 'success' && $response['data'][0]['status'] === 'online') {
            // Nomor valid dan aktif, lanjutkan memperbarui data saksi
            $data->update($request->all());

            return redirect()->route('saksi')->with('success', 'No WhatsApp updated successfully.');
        } else {
            // Nomor tidak valid atau tidak aktif, kembali dengan pesan kesalahan
            return redirect()->route('saksi')->with('error', 'Nomor WhatsApp tidak valid atau tidak aktif.');
        }
   }

    public function koortps($id){
        $koorTps = Saksi::findOrFail($id);
        $koorTps->delete();
    
        return redirect()->route('saksi');
    }
}
