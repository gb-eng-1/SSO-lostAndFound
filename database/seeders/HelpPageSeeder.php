<?php

namespace Database\Seeders;

use App\Models\ProcessGuide;
use App\Models\SupportContact;
use Illuminate\Database\Seeder;

class HelpPageSeeder extends Seeder
{
    public function run(): void
    {
        // ── Process Guides ─────────────────────────────────────────────────────

        $guides = [
            // How to Report a Lost Item
            [
                'section'                => 'report_lost',
                'title'                  => 'Submit a Lost Item Report',
                'step_number'            => 1,
                'instruction'            => 'Log in to the UB Lost and Found portal using your university email and password.',
                'estimated_time_minutes' => 2,
                'faq'                    => json_encode([
                    ['q' => 'What if I forgot my password?', 'a' => 'Contact the Lost and Found office directly at the information below.'],
                ]),
                'troubleshooting'        => json_encode([]),
            ],
            [
                'section'                => 'report_lost',
                'title'                  => 'Fill in the Report Form',
                'step_number'            => 2,
                'instruction'            => 'Click "Report Lost Item" on the My Reports page. Fill in the category, color, brand, date lost, department, contact number, and a detailed description of the item. Attach a photo if available.',
                'estimated_time_minutes' => 5,
                'faq'                    => json_encode([
                    ['q' => 'What information should I include?', 'a' => 'Include as many identifying details as possible — unique markings, serial numbers, or anything that distinguishes your item.'],
                ]),
                'troubleshooting'        => json_encode([
                    ['issue' => 'Form submission fails', 'solution' => 'Make sure Contact Number and Department are filled in. These are required.'],
                ]),
            ],
            [
                'section'                => 'report_lost',
                'title'                  => 'Wait for a Match',
                'step_number'            => 3,
                'instruction'            => 'Once submitted, the Lost and Found office will search for matching found items. You will receive a notification when a potential match is found.',
                'estimated_time_minutes' => null,
                'faq'                    => json_encode([
                    ['q' => 'How long does matching take?', 'a' => 'Matching depends on item availability. You will be notified as soon as a match is identified.'],
                ]),
                'troubleshooting'        => json_encode([]),
            ],

            // How to Search for Found Items
            [
                'section'                => 'search_found',
                'title'                  => 'Browse Available Items',
                'step_number'            => 1,
                'instruction'            => 'Click "Browse Items" in the sidebar. You will see a grid of all currently unclaimed found items held in the Lost and Found office.',
                'estimated_time_minutes' => 3,
                'faq'                    => json_encode([
                    ['q' => 'Can I browse without logging in?', 'a' => 'No. You must be logged in as a student to browse items.'],
                ]),
                'troubleshooting'        => json_encode([]),
            ],
            [
                'section'                => 'search_found',
                'title'                  => 'Use Filters',
                'step_number'            => 2,
                'instruction'            => 'Use the category filter and search box to narrow down results by type, color, brand, or keyword.',
                'estimated_time_minutes' => 2,
                'faq'                    => json_encode([]),
                'troubleshooting'        => json_encode([]),
            ],
            [
                'section'                => 'search_found',
                'title'                  => 'Check the Matched Tab',
                'step_number'            => 3,
                'instruction'            => 'If the admin has already matched a found item to one of your lost reports, you will see it on the "Matched" tab of My Reports.',
                'estimated_time_minutes' => 1,
                'faq'                    => json_encode([
                    ['q' => 'Why is my item not showing in Browse?', 'a' => 'Items that have already been claimed or disposed will not appear.'],
                ]),
                'troubleshooting'        => json_encode([]),
            ],

            // How to Claim an Item
            [
                'section'                => 'claim_item',
                'title'                  => 'Go to My Reports → Matched Tab',
                'step_number'            => 1,
                'instruction'            => 'Navigate to My Reports and click the "Matched" tab. Items matched to your lost reports will appear here.',
                'estimated_time_minutes' => 1,
                'faq'                    => json_encode([]),
                'troubleshooting'        => json_encode([]),
            ],
            [
                'section'                => 'claim_item',
                'title'                  => 'Submit a Claim',
                'step_number'            => 2,
                'instruction'            => 'Click "Submit Claim" on the matched item. Upload a proof photo (e.g. a photo of you with a receipt, or any proof of ownership) and write a brief description of how you can prove the item is yours.',
                'estimated_time_minutes' => 5,
                'faq'                    => json_encode([
                    ['q' => 'What counts as proof?', 'a' => 'A receipt, purchase confirmation, photo of you with the item, or any other document proving ownership.'],
                    ['q' => 'Is a proof photo required?', 'a' => 'Yes. A photo is required to submit a claim.'],
                ]),
                'troubleshooting'        => json_encode([
                    ['issue' => 'Claim button is missing', 'solution' => 'Claims can only be submitted for items matched by the admin. File a lost report first.'],
                ]),
            ],
            [
                'section'                => 'claim_item',
                'title'                  => 'Pick Up Your Item',
                'step_number'            => 3,
                'instruction'            => 'Once the admin approves your claim, visit the Lost and Found office to pick up your item. Bring a valid ID. The admin will confirm the claim and record the handover.',
                'estimated_time_minutes' => null,
                'faq'                    => json_encode([
                    ['q' => 'How do I know my claim was approved?', 'a' => 'You will receive an in-app notification when the admin approves or rejects your claim.'],
                ]),
                'troubleshooting'        => json_encode([]),
            ],
        ];

        foreach ($guides as $guide) {
            ProcessGuide::firstOrCreate(
                ['section' => $guide['section'], 'step_number' => $guide['step_number']],
                $guide
            );
        }

        // ── Support Contacts ────────────────────────────────────────────────────

        $contacts = [
            [
                'name'            => 'Lost and Found Office',
                'email'           => 'lostandfound@ub.edu.ph',
                'phone'           => '(043) 723-0900',
                'office_location' => 'Main Building, Ground Floor',
                'department'      => 'Student Affairs Office',
                'role'            => 'Lost & Found Administrator',
                'office_hours'    => 'Monday – Friday, 8:00 AM – 5:00 PM',
            ],
            [
                'name'            => 'Student Affairs Office',
                'email'           => 'studentaffairs@ub.edu.ph',
                'phone'           => '(043) 723-0900 x101',
                'office_location' => 'Admin Building, 2nd Floor',
                'department'      => 'Student Affairs',
                'role'            => 'General Student Concerns',
                'office_hours'    => 'Monday – Friday, 8:00 AM – 5:00 PM',
            ],
            [
                'name'            => 'Security Office',
                'email'           => 'security@ub.edu.ph',
                'phone'           => '(043) 723-0900 x102',
                'office_location' => 'Main Gate',
                'department'      => 'Campus Security',
                'role'            => 'Campus Safety & Security',
                'office_hours'    => '24/7',
            ],
        ];

        foreach ($contacts as $contact) {
            SupportContact::firstOrCreate(
                ['email' => $contact['email']],
                $contact
            );
        }
    }
}
