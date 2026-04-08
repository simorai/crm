<?php

namespace Database\Seeders;

use App\Models\AiSuggestion;
use App\Models\AutomationRule;
use App\Models\CalendarEvent;
use App\Models\Deal;
use App\Models\DealProduct;
use App\Models\EmailTemplate;
use App\Models\Entity;
use App\Models\LeadForm;
use App\Models\Person;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- Users ---
        $simao = User::firstOrCreate(
            ['email' => env('SEED_USER_EMAIL')],
            [
                'name' => env('SEED_USER_NAME'),
                'password' => Hash::make(env('SEED_USER_PASSWORD')),
                'email_verified_at' => now(),
            ]
        );

        $demo = User::firstOrCreate(
            ['email' => env('SEED_DEMO_EMAIL')],
            [
                'name'              => env('SEED_DEMO_NAME'),
                'password'          => Hash::make(env('SEED_DEMO_PASSWORD')),
                'email_verified_at' => now(),
            ]
        );

        // --- Tenant ---
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'inovcorp'],
            [
                'name'     => 'InovCorp',
                'owner_id' => $simao->id,
                'settings' => null,
            ]
        );

        TenantUser::firstOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $simao->id],
            ['role' => 'owner']
        );

        TenantUser::firstOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $demo->id],
            ['role' => 'member']
        );

        // --- Products ---
        $products = [
            ['name' => 'CRM Starter Plan',      'description' => 'Essential CRM features for small teams.',          'price' => 29.00],
            ['name' => 'CRM Professional Plan',  'description' => 'Advanced pipeline and automation tools.',          'price' => 79.00],
            ['name' => 'CRM Enterprise Plan',    'description' => 'Full-featured CRM with dedicated support.',        'price' => 199.00],
            ['name' => 'Onboarding Package',     'description' => 'Guided setup and training sessions.',              'price' => 499.00],
            ['name' => 'API Integration Add-on', 'description' => 'Connect InovCorp with third-party services.',      'price' => 49.00],
        ];

        $createdProducts = [];
        foreach ($products as $p) {
            $createdProducts[] = Product::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $p['name']],
                ['description' => $p['description'], 'price' => $p['price']]
            );
        }

        // --- Entities (Companies) ---
        $entityData = [
            ['name' => 'Nexus Technologies',  'vat' => 'PT501234567', 'email' => 'contact@nexustech.pt',   'phone' => '+351 21 000 1001', 'address' => 'Av. da Liberdade 200, Lisbon',    'status' => 'active'],
            ['name' => 'Orbit Solutions',     'vat' => 'PT502345678', 'email' => 'info@orbitsolutions.pt', 'phone' => '+351 22 000 2002', 'address' => 'Rua de Santa Catarina 50, Porto', 'status' => 'active'],
            ['name' => 'PeakDrive Logistics', 'vat' => 'PT503456789', 'email' => 'hello@peakdrive.pt',     'phone' => '+351 21 000 3003', 'address' => 'Parque das Nações, Lisbon',        'status' => 'active'],
            ['name' => 'BlueWave Media',      'vat' => 'PT504567890', 'email' => 'media@bluewave.pt',      'phone' => '+351 21 000 4004', 'address' => 'Av. 5 de Outubro 120, Lisbon',    'status' => 'lead'],
            ['name' => 'Solaris Energy',      'vat' => 'PT505678901', 'email' => 'info@solarisegy.pt',     'phone' => '+351 26 000 5005', 'address' => 'Zona Industrial de Évora',         'status' => 'inactive'],
        ];

        $entities = [];
        foreach ($entityData as $e) {
            $entities[] = Entity::firstOrCreate(
                ['tenant_id' => $tenant->id, 'vat' => $e['vat']],
                array_merge(['tenant_id' => $tenant->id], $e)
            );
        }

        // --- People (Contacts) ---
        $contactData = [
            ['entity' => 0, 'name' => 'Ana Costa',        'email' => 'ana.costa@nexustech.pt',      'phone' => '+351 91 100 1001', 'position' => 'CEO'],
            ['entity' => 0, 'name' => 'Miguel Fernandes', 'email' => 'miguel.f@nexustech.pt',       'phone' => '+351 91 100 1002', 'position' => 'CTO'],
            ['entity' => 1, 'name' => 'Sofia Rodrigues',  'email' => 'sofia.r@orbitsolutions.pt',   'phone' => '+351 93 200 2001', 'position' => 'Sales Director'],
            ['entity' => 1, 'name' => 'João Almeida',     'email' => 'j.almeida@orbitsolutions.pt', 'phone' => '+351 93 200 2002', 'position' => 'Account Manager'],
            ['entity' => 2, 'name' => 'Carla Mendes',     'email' => 'c.mendes@peakdrive.pt',       'phone' => '+351 91 300 3001', 'position' => 'Operations Director'],
            ['entity' => 3, 'name' => 'Pedro Santos',     'email' => 'pedro.s@bluewave.pt',         'phone' => '+351 96 400 4001', 'position' => 'Marketing Manager'],
            ['entity' => 4, 'name' => 'Inês Oliveira',    'email' => 'ines.o@solarisegy.pt',        'phone' => '+351 91 500 5001', 'position' => 'Business Development'],
        ];

        $contacts = [];
        foreach ($contactData as $c) {
            $contacts[] = Person::firstOrCreate(
                ['tenant_id' => $tenant->id, 'email' => $c['email']],
                [
                    'tenant_id' => $tenant->id,
                    'entity_id' => $entities[$c['entity']]->id,
                    'name'      => $c['name'],
                    'phone'     => $c['phone'],
                    'position'  => $c['position'],
                ]
            );
        }

        // --- Email Templates ---
        $templates = [
            [
                'name'    => 'Welcome Follow-Up',
                'subject' => 'Welcome to InovCorp — next steps',
                'body'    => "Hi {{name}},\n\nThank you for your interest in InovCorp CRM. We'd love to schedule a quick call to walk you through our platform.\n\nBest regards,\nInovCorp Team",
                'type'    => 'follow_up',
            ],
            [
                'name'    => 'Proposal Sent',
                'subject' => 'Your InovCorp Proposal is Ready',
                'body'    => "Hi {{name}},\n\nPlease find the attached proposal for {{deal_title}}. Let us know if you have any questions.\n\nBest regards,\nInovCorp Sales Team",
                'type'    => 'proposal',
            ],
            [
                'name'    => 'Deal Closed — Thank You',
                'subject' => 'Welcome aboard, {{company}}!',
                'body'    => "Hi {{name}},\n\nWe're thrilled to have {{company}} join the InovCorp family. Your onboarding team will reach out within 24 hours.\n\nWarm regards,\nInovCorp Customer Success",
                'type'    => 'closed',
            ],
        ];

        $emailTemplates = [];
        foreach ($templates as $t) {
            $emailTemplates[] = EmailTemplate::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $t['name']],
                array_merge(['tenant_id' => $tenant->id], $t)
            );
        }

        // --- Deals ---
        $deals = [];
        $dealData = [
            [
                'entity'   => 0,
                'person'   => 0,
                'title'    => 'Nexus Technologies — Enterprise Licence',
                'value'    => 9960.00,
                'stage'    => 'proposal',
                'probability' => 65,
                'expected_close_date' => now()->addDays(30),
                'notes'    => 'Client is evaluating two other vendors. Decision expected end of month.',
                'products' => [['idx' => 2, 'qty' => 4, 'price' => 199.00], ['idx' => 4, 'qty' => 4, 'price' => 49.00]],
            ],
            [
                'entity'   => 1,
                'person'   => 2,
                'title'    => 'Orbit Solutions — Professional Upgrade',
                'value'    => 948.00,
                'stage'    => 'negotiation',
                'probability' => 80,
                'expected_close_date' => now()->addDays(14),
                'notes'    => 'Currently on Starter, upgrading 10 seats to Professional.',
                'products' => [['idx' => 1, 'qty' => 10, 'price' => 79.00]],
            ],
            [
                'entity'   => 2,
                'person'   => 4,
                'title'    => 'PeakDrive — Starter + Onboarding',
                'value'    => 1043.00,
                'stage'    => 'won',
                'probability' => 100,
                'expected_close_date' => now()->subDays(5),
                'notes'    => 'Closed. Onboarding scheduled for next week.',
                'products' => [['idx' => 0, 'qty' => 1, 'price' => 29.00], ['idx' => 3, 'qty' => 1, 'price' => 499.00]],
            ],
            [
                'entity'   => 3,
                'person'   => 5,
                'title'    => 'BlueWave Media — CRM Evaluation',
                'value'    => 474.00,
                'stage'    => 'contact',
                'probability' => 40,
                'expected_close_date' => now()->addDays(45),
                'notes'    => 'Marketing-driven lead. Demo call booked.',
                'products' => [['idx' => 1, 'qty' => 6, 'price' => 79.00]],
            ],
            [
                'entity'   => 4,
                'person'   => 6,
                'title'    => 'Solaris Energy — API Integration',
                'value'    => 147.00,
                'stage'    => 'lead',
                'probability' => 20,
                'expected_close_date' => now()->addDays(60),
                'notes'    => 'Inbound lead via web form.',
                'products' => [['idx' => 4, 'qty' => 3, 'price' => 49.00]],
            ],
        ];

        foreach ($dealData as $d) {
            $deal = Deal::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'title' => $d['title']],
                [
                    'tenant_id'           => $tenant->id,
                    'entity_id'           => $entities[$d['entity']]->id,
                    'person_id'           => $contacts[$d['person']]->id,
                    'owner_id'            => $simao->id,
                    'value'               => $d['value'],
                    'stage'               => $d['stage'],
                    'probability'         => $d['probability'],
                    'expected_close_date' => $d['expected_close_date'],
                    'notes'               => $d['notes'],
                ]
            );
            $deals[] = $deal;

            foreach ($d['products'] as $p) {
                DealProduct::firstOrCreate(
                    ['deal_id' => $deal->id, 'product_id' => $createdProducts[$p['idx']]->id],
                    ['quantity' => $p['qty'], 'price' => $p['price']]
                );
            }
        }

        // --- Calendar Events ---
        $calendarEvents = [
            [
                'title'       => 'Demo call — Nexus Technologies',
                'description' => 'Walk Ana Costa through the Enterprise plan features.',
                'location'    => 'Google Meet',
                'start_at'    => now()->addDays(3)->setTime(10, 0),
                'end_at'      => now()->addDays(3)->setTime(11, 0),
                'all_day'     => false,
            ],
            [
                'title'       => 'Contract negotiation — Orbit Solutions',
                'description' => 'Finalise pricing for 10-seat Professional upgrade.',
                'location'    => 'Orbit HQ, Porto',
                'start_at'    => now()->addDays(7)->setTime(14, 0),
                'end_at'      => now()->addDays(7)->setTime(15, 0),
                'all_day'     => false,
            ],
            [
                'title'       => 'PeakDrive Onboarding Kickoff',
                'description' => 'Initial setup and team training session.',
                'location'    => 'Zoom',
                'start_at'    => now()->addDays(10)->setTime(9, 0),
                'end_at'      => now()->addDays(10)->setTime(11, 0),
                'all_day'     => false,
            ],
            [
                'title'       => 'BlueWave Media — Discovery Call',
                'description' => 'Understand their requirements and present InovCorp CRM.',
                'location'    => 'Teams',
                'start_at'    => now()->addDays(2)->setTime(15, 30),
                'end_at'      => now()->addDays(2)->setTime(16, 0),
                'all_day'     => false,
            ],
        ];

        foreach ($calendarEvents as $ev) {
            CalendarEvent::firstOrCreate(
                ['tenant_id' => $tenant->id, 'title' => $ev['title']],
                array_merge(['tenant_id' => $tenant->id, 'owner_id' => $simao->id], $ev)
            );
        }

        // --- Automation Rules ---
        $automationRules = [
            [
                'name'       => 'No Activity Alert',
                'trigger'    => 'deal_idle',
                'conditions' => [],
                'actions'    => [['type' => 'notify_owner', 'message' => 'Deal idle for 7+ days. Schedule a follow-up.']],
                'is_active'  => true,
            ],
            [
                'name'       => 'Proposal Stage Follow-Up',
                'trigger'    => 'deal_stage_changed',
                'conditions' => [['field' => 'stage', 'operator' => 'equals', 'value' => 'proposal']],
                'actions'    => [['type' => 'send_email', 'template' => 'follow_up'], ['type' => 'create_activity', 'title' => 'Follow up on proposal']],
                'is_active'  => true,
            ],
            [
                'name'       => 'Deal Won — Welcome Email',
                'trigger'    => 'deal_stage_changed',
                'conditions' => [['field' => 'stage', 'operator' => 'equals', 'value' => 'won']],
                'actions'    => [['type' => 'send_email', 'template' => 'closed']],
                'is_active'  => true,
            ],
        ];

        foreach ($automationRules as $rule) {
            AutomationRule::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $rule['name']],
                array_merge(['tenant_id' => $tenant->id], $rule)
            );
        }

        // --- Lead Forms ---
        $leadForms = [
            [
                'name'      => 'Contact Us',
                'fields'    => [
                    ['label' => 'Name',    'type' => 'text',     'required' => true],
                    ['label' => 'Email',   'type' => 'email',    'required' => true],
                    ['label' => 'Message', 'type' => 'textarea', 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name'      => 'Demo Request',
                'fields'    => [
                    ['label' => 'Full Name',   'type' => 'text',  'required' => true],
                    ['label' => 'Work Email',  'type' => 'email', 'required' => true],
                    ['label' => 'Company',     'type' => 'text',  'required' => false],
                    ['label' => 'Phone',       'type' => 'phone', 'required' => false],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($leadForms as $lf) {
            LeadForm::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $lf['name']],
                array_merge(['tenant_id' => $tenant->id], $lf)
            );
        }

        // --- AI Suggestions ---
        $aiSuggestions = [
            [
                'deal_id'   => $deals[0]->id ?? null,
                'type'      => 'follow_up',
                'rationale' => 'No activity in 5 days. A quick check-in could re-engage the prospect and keep the deal moving.',
                'status'    => 'pending',
            ],
            [
                'deal_id'   => $deals[1]->id ?? null,
                'type'      => 'close_now',
                'rationale' => 'Deal is in negotiation with 80% probability and strong buying signals detected.',
                'status'    => 'pending',
            ],
            [
                'deal_id'   => $deals[0]->id ?? null,
                'type'      => 'schedule_call',
                'rationale' => 'Schedule a discovery call to address remaining objections before end of month.',
                'status'    => 'pending',
            ],
        ];

        // Use deal+type combo as unique key; only insert missing ones
        foreach ($aiSuggestions as $s) {
            if (! empty($s['deal_id'])) {
                AiSuggestion::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'deal_id' => $s['deal_id'], 'type' => $s['type']],
                    array_merge(['tenant_id' => $tenant->id], $s)
                );
            }
        }
    }
}
