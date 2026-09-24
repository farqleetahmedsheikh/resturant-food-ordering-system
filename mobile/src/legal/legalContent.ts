export type LegalSection = {
  title: string;
  body: string[];
};

export const legalUpdatedAt = '24 September 2026';

export const privacySections: LegalSection[] = [
  {
    title: 'Short version',
    body: [
      'We collect the information needed to create accounts, prepare orders, take payment through Stripe, deliver food, provide support, improve security, and meet legal obligations.',
      'We do not sell your personal information.',
    ],
  },
  {
    title: 'Information we collect',
    body: [
      'This may include your name, email, phone, password, role, cart items, order history, delivery address, coordinates you choose to provide, delivery notes, support messages, app notification tokens, device information, IP address, and security logs.',
      'Card payments are processed by Stripe. We receive payment status and transaction references, but we do not store your full card number on our servers.',
    ],
  },
  {
    title: 'How we use information',
    body: [
      'We use personal information to manage accounts, process orders, take payments, assign delivery, send receipts and order updates, provide support, maintain security, prevent misuse, improve reliability, and comply with legal, accounting, and tax obligations.',
    ],
  },
  {
    title: 'Sharing',
    body: [
      'We share information only where needed to run the service, complete your order, or meet legal obligations. This may include restaurant staff, assigned riders, Stripe, hosting and email providers, technical service providers, professional advisers, regulators, or authorities where required by law.',
    ],
  },
  {
    title: 'Access and contact',
    body: [
      'You may ask to access or correct personal information we hold about you, or contact us with a privacy complaint. We may need to verify your identity before acting on a request.',
    ],
  },
];

export const termsSections: LegalSection[] = [
  {
    title: 'Using the service',
    body: [
      'By using the Arcade Kebab House website or mobile app, creating an account, browsing the menu, placing an order, making payment, or contacting support, you agree to these terms and the Privacy Policy.',
    ],
  },
  {
    title: 'Accounts and orders',
    body: [
      'You are responsible for accurate account, contact, order, pickup, and delivery information. Please review items, quantities, notes, address, and contact details before paying.',
      'We may decline or cancel an order where items are unavailable, payment is not authorised, delivery information is incomplete, the order appears fraudulent or abusive, or circumstances outside our control prevent fulfilment.',
    ],
  },
  {
    title: 'Payments',
    body: [
      'Online card payments are processed securely through Stripe. You must be authorised to use the payment method you provide. We do not store full card numbers on our servers.',
    ],
  },
  {
    title: 'Delivery, cancellations, and refunds',
    body: [
      'Delivery estimates are estimates only and may be affected by preparation time, traffic, weather, rider availability, or incomplete address details.',
      'Because food is prepared to order, cancellation or change requests may not be possible once preparation has started. If there is a problem, contact us as soon as possible with your order details.',
      'Nothing in these terms limits rights you may have under the Australian Consumer Law, including consumer guarantees that cannot lawfully be excluded.',
    ],
  },
  {
    title: 'Allergens and acceptable use',
    body: [
      'Food may be prepared in kitchens that handle allergens. Please contact us before ordering if you have allergies or dietary requirements. We cannot guarantee that any item is free from traces of allergens.',
      'You must not misuse the website or app, attempt unauthorised access, interfere with security, submit false orders, harass staff or riders, or use the service for unlawful purposes.',
    ],
  },
];
