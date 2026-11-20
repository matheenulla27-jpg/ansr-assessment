import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { EnquiryFormData } from '../../types/catalogue';

interface EnquiryFormProps {
    productId: number;
    onClose: () => void;
}

export default function EnquiryForm({ productId, onClose }: EnquiryFormProps) {
    const form = useForm<EnquiryFormData>({
        product_id: productId,
        name: '',
        email: '',
        phone: '',
        message: '',
    });

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.post('/products/enquiries', { preserveScroll: true });
    }

    if (form.wasSuccessful) {
        return (
            <div
                role="status"
                className="space-y-5 rounded-lg border border-green-200 bg-green-50 p-5 text-green-900"
            >
                <div className="space-y-2">
                    <h3 className="text-xl font-semibold">Thank you</h3>
                    <p>Your enquiry has been sent.</p>
                </div>
                <button
                    type="button"
                    onClick={() => {
                        form.resetAndClearErrors();
                        onClose();
                    }}
                    className="rounded-lg bg-slate-900 px-5 py-3 font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                >
                    Close
                </button>
            </div>
        );
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-5">
            <div className="space-y-2">
                <label htmlFor="enquiry-name" className="block font-medium">
                    Name
                </label>
                <input
                    id="enquiry-name"
                    name="name"
                    autoComplete="name"
                    value={form.data.name}
                    onChange={(event) => form.setData('name', event.target.value)}
                    aria-invalid={Boolean(form.errors.name)}
                    aria-describedby={form.errors.name ? 'enquiry-name-error' : undefined}
                    className="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
                />
                {form.errors.name && (
                    <p id="enquiry-name-error" className="text-sm text-red-700">
                        {form.errors.name}
                    </p>
                )}
            </div>

            <div className="space-y-2">
                <label htmlFor="enquiry-email" className="block font-medium">
                    Email
                </label>
                <input
                    id="enquiry-email"
                    name="email"
                    type="email"
                    autoComplete="email"
                    value={form.data.email}
                    onChange={(event) => form.setData('email', event.target.value)}
                    aria-invalid={Boolean(form.errors.email)}
                    aria-describedby={form.errors.email ? 'enquiry-email-error' : undefined}
                    className="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
                />
                {form.errors.email && (
                    <p id="enquiry-email-error" className="text-sm text-red-700">
                        {form.errors.email}
                    </p>
                )}
            </div>

            <div className="space-y-2">
                <label htmlFor="enquiry-phone" className="block font-medium">
                    Phone <span className="font-normal text-slate-500">(optional)</span>
                </label>
                <input
                    id="enquiry-phone"
                    name="phone"
                    type="tel"
                    autoComplete="tel"
                    value={form.data.phone}
                    onChange={(event) => form.setData('phone', event.target.value)}
                    aria-invalid={Boolean(form.errors.phone)}
                    aria-describedby={form.errors.phone ? 'enquiry-phone-error' : undefined}
                    className="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
                />
                {form.errors.phone && (
                    <p id="enquiry-phone-error" className="text-sm text-red-700">
                        {form.errors.phone}
                    </p>
                )}
            </div>

            <div className="space-y-2">
                <label htmlFor="enquiry-message" className="block font-medium">
                    Message
                </label>
                <textarea
                    id="enquiry-message"
                    name="message"
                    rows={5}
                    value={form.data.message}
                    onChange={(event) => form.setData('message', event.target.value)}
                    aria-invalid={Boolean(form.errors.message)}
                    aria-describedby={form.errors.message ? 'enquiry-message-error' : undefined}
                    className="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
                />
                {form.errors.message && (
                    <p id="enquiry-message-error" className="text-sm text-red-700">
                        {form.errors.message}
                    </p>
                )}
            </div>

            {form.errors.product_id && (
                <p role="alert" className="text-sm text-red-700">
                    {form.errors.product_id}
                </p>
            )}

            <button
                type="submit"
                disabled={form.processing}
                className="rounded-lg bg-slate-900 px-5 py-3 font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {form.processing ? 'Sending…' : 'Send enquiry'}
            </button>
        </form>
    );
}
