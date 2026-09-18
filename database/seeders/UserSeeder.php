<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Anak Agung Made S., S.T',
                'nip' => '197412272003121005',
                'email' => 'superadmin@kalselprov.go.id',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'regency_id' => null,
                'phone' => '081234567890',
                'position' => 'Super Admin Provinsi (Bidang Ketransmigrasian)',
                'is_active' => true,
            ],
            [
                'name' => 'Hj. Ina Yuliani, S.Sos, M.Si, M.IP',
                'nip' => '196907291990102001',
                'email' => 'kadis@kalselprov.go.id',
                'password' => Hash::make('password123'),
                'role' => 'eksekutif',
                'regency_id' => null,
                'phone' => '081234567891',
                'position' => 'Kepala Bidang Ketransmigrasian Disnakertrans Prov. Kalsel',
                'is_active' => true,
            ],
            [
                'name' => 'Ahmad Zaini, S.AP',
                'nip' => '198506152010011012',
                'email' => 'operator.tapin@kalselprov.go.id',
                'password' => Hash::make('password123'),
                'role' => 'operator_kabupaten',
                'regency_id' => 1, // Tapin
                'phone' => '081234567892',
                'position' => 'Operator Disnakertrans Kabupaten Tapin',
                'is_active' => true,
            ],
            [
                'name' => 'Tim Pengendali Hak Tanah Kanwil BPN',
                'nip' => '198001012005011001',
                'email' => 'kanwil.bpn@atrbpn.go.id',
                'password' => Hash::make('password123'),
                'role' => 'mitra_bpn',
                'regency_id' => null,
                'phone' => '081234567893',
                'position' => 'Mitra Sektoral - Kanwil BPN Provinsi Kalsel',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
