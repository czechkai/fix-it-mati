<?php
/**
 * Renders an SVG icon from the Lucide icon set.
 *
 * @param string $name The name of the icon (e.g., 'home', 'arrow-left').
 * @param int $size The size of the icon in pixels.
 * @param string $class Additional CSS classes to apply to the SVG element.
 * @return string The SVG markup.
 */
function get_icon(string $name, int $size = 24, string $class = ''): string
{
    $icons = [
        'arrow-left' => '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
        'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
        'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'trash-2' => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>',
        'edit-2' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'navigation' => '<polygon points="3 11 22 2 13 21 11 13 3 11"/>'
    ];

    if (!isset($icons[$name])) {
        return ''; // Return empty string if icon not found
    }

    return sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="%s">%s</svg>',
        $size,
        $size,
        htmlspecialchars($class),
        $icons[$name]
    );
}

// Mock Data: In a real application, this would come from the database.
$addresses = [
    [
        'id' => 1,
        'label' => "Home",
        'type' => "Residential",
        'barangay' => "Brgy. Central",
        'street' => "123 Main Street Extension",
        'details' => "Blue gate near the bakery",
        'isDefault' => true
    ],
    [
        'id' => 2,
        'label' => "Rental Apartment",
        'type' => "Residential",
        'barangay' => "Brgy. Dahican",
        'street' => "Purok 4, Coastal Road",
        'details' => "2nd floor, door 4",
        'isDefault' => false
    ],
    [
        'id' => 3,
        'label' => "Downtown Office",
        'type' => "Commercial",
        'barangay' => "Brgy. Matiao",
        'street' => "Rizal Avenue",
        'details' => "Beside City Hardware",
        'isDefault' => false
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Addresses - FixItMati</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* You can add any additional global styles here if needed */
        body {
            font-family: sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <div id="app">
<!-- --- HEADER --- -->
<div class="bg-white border-b border-slate-200 sticky top-0 z-30 px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <a href="/user-dashboard.php" class="text-slate-500 hover:text-blue-600 transition-colors">
            <?= get_icon('arrow-left', 20) ?>
        </a>
        <h1 class="text-lg font-bold text-slate-800">Service Addresses</h1>
    </div>
    <button id="openModalBtn" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors">
        <?= get_icon('plus', 16) ?> <span class="hidden sm:inline">Add New Address</span>
    </button>
</div>

<!-- --- MAIN CONTENT --- -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Address List -->
    <div class="space-y-4">
        <?php foreach ($addresses as $addr) : ?>
            <div class="bg-white rounded-xl border transition-all duration-200 relative overflow-hidden group <?= $addr['isDefault'] ? 'border-blue-500 shadow-md ring-1 ring-blue-500/20' : 'border-slate-200 shadow-sm hover:border-blue-300 hover:shadow-md' ?>">
                <div class="p-5 flex items-start gap-4">

                    <!-- Icon Box -->
                    <div class="p-3 rounded-lg flex-shrink-0 <?= $addr['isDefault'] ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-500' ?>">
                        <?php
                        if (stripos($addr['label'], 'home') !== false) echo get_icon('home', 20);
                        elseif (stripos($addr['label'], 'office') !== false) echo get_icon('briefcase', 20);
                        else echo get_icon('map-pin', 20);
                        ?>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-slate-800 text-base"><?= htmlspecialchars($addr['label']) ?></h3>
                            <?php if ($addr['isDefault']) : ?>
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-blue-200 flex items-center gap-1">
                                    <?= get_icon('check', 10) ?> Default
                                </span>
                            <?php endif; ?>
                            <span class="text-xs text-slate-400 border border-slate-200 px-1.5 rounded uppercase">
                                <?= htmlspecialchars($addr['type']) ?>
                            </span>
                        </div>

                        <p class="text-sm text-slate-600 font-medium"><?= htmlspecialchars($addr['street']) ?></p>
                        <p class="text-sm text-slate-500"><?= htmlspecialchars($addr['barangay']) ?>, Mati City</p>

                        <?php if (!empty($addr['details'])) : ?>
                            <div class="flex items-center gap-1 mt-2 text-xs text-slate-400">
                                <?= get_icon('navigation', 12) ?>
                                <span>Note: <?= htmlspecialchars($addr['details']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Menu -->
                    <div class="flex flex-col gap-2">
                        <?php if (!$addr['isDefault']) : ?>
                            <button class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Set as Default">
                                <?= get_icon('star', 18) ?>
                            </button>
                        <?php endif; ?>
                        <button class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                            <?= get_icon('edit-2', 18) ?>
                        </button>
                    </div>

                </div>

                <!-- Context Footer -->
                <div class="border-t border-slate-50 bg-slate-50/50 px-5 py-2 flex justify-between items-center text-xs text-slate-500">
                    <span>Coordinates: 6.95° N, 126.21° E</span>
                    <button class="text-red-500 hover:text-red-700 hover:underline flex items-center gap-1">
                        <?= get_icon('trash-2', 12) ?> Remove
                    </button>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- Empty State Helper -->
    <div class="mt-8 p-4 bg-blue-50 border border-blue-100 rounded-xl flex gap-3 text-blue-800">
        <?= get_icon('map-pin', 24, 'flex-shrink-0') ?>
        <div class="text-sm">
            <p class="font-bold">Tip: Accurate locations help us find you faster.</p>
            <p class="opacity-80 mt-1">
                When adding an address, try to include specific landmarks (e.g., "Green gate," "Near the chapel") to help our field technicians.
            </p>
        </div>
    </div>

</div>

<!-- --- ADD ADDRESS MODAL --- -->
<div id="addAddressModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800">Add New Address</h3>
            <button id="closeModalBtn" class="text-slate-400 hover:text-slate-600">
                <?= get_icon('x', 20) ?>
            </button>
        </div>

        <div class="p-6 space-y-4">

            <!-- Label & Type -->
            <div class="flex gap-4">
                <div class="flex-1 space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase">Label</label>
                    <input type="text" placeholder="e.g. Home, Office" class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="w-1/3 space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase">Type</label>
                    <select class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none bg-white">
                        <option>Residential</option>
                        <option>Commercial</option>
                    </select>
                </div>
            </div>

            <!-- Barangay Dropdown -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase">Barangay</label>
                <select class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Barangay...</option>
                    <option>Brgy. Central</option>
                    <option>Brgy. Dahican</option>
                    <option>Brgy. Matiao</option>
                    <option>Brgy. Sainz</option>
                    <option>Brgy. Tamisan</option>
                </select>
            </div>

            <!-- Street Address -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase">Street / House No.</label>
                <input type="text" placeholder="House 123, Purok 1..." class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <!-- Landmarks -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase">Landmark / Notes</label>
                <textarea rows="2" placeholder="Near the yellow store..." class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>

            <!-- Map Placeholder -->
            <div class="h-32 bg-slate-100 rounded-lg border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-400 cursor-pointer hover:bg-slate-200 hover:border-slate-300 transition-colors">
                <?= get_icon('map-pin', 24, 'mb-2') ?>
                <span class="text-xs font-bold">Pin Location on Map</span>
                <span class="text-[10px]">(Optional but recommended)</span>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="defaultAddr" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" />
                <label for="defaultAddr" class="text-sm text-slate-600">Set as default address</label>
            </div>

            <div class="pt-2">
                <button id="saveAddressBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-sm transition-colors">
                    Save Address
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addAddressModal');
        const openBtn = document.getElementById('openModalBtn');
        const closeBtn = document.getElementById('closeModalBtn');
        const saveBtn = document.getElementById('saveAddressBtn');
        const backdrop = modal.querySelector('.backdrop-blur-sm');

        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => modal.classList.add('hidden');

        openBtn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);

        saveBtn.addEventListener('click', () => {
            alert('Address Added!'); // Replace with actual form submission logic
            closeModal();
        });

        // Close modal on escape key press
        document.addEventListener('keydown', (e) => {
            if (e.key === "Escape" && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    });
</script>

<?php require 'partials/footer.php'; ?>