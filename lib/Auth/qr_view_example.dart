// import 'package:flutter/material.dart';
// import 'package:mobile_scanner/mobile_scanner.dart';

// class QRViewExample extends StatefulWidget {
//   const QRViewExample({super.key});

//   @override
//   State<StatefulWidget> createState() => _QRViewExampleState();
// }

// class _QRViewExampleState extends State<QRViewExample> {
//   MobileScannerController controller = MobileScannerController();
//   String? scannedData;

//   @override
//   Widget build(BuildContext context) {
//     return Scaffold(
//       body: MobileScanner(
//         controller: controller,
//         onDetect: (capture) {
//           final List<Barcode> barcodes = capture.barcodes;
//           for (final barcode in barcodes) {
//             setState(() {
//               scannedData = barcode.rawValue;
//             });
//             controller.stop();
//             Navigator.pop(context, scannedData);
//           }
//         },
//       ),
//     );
//   }

//   @override
//   void dispose() {
//     controller.dispose();
//     super.dispose();
//   }
// }