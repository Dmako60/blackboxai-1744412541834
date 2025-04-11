import 'package:flutter/material.dart';

enum CustomButtonType {
  primary,
  secondary,
  outline,
  text,
}

class CustomButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final CustomButtonType type;
  final bool isLoading;
  final bool fullWidth;
  final IconData? icon;
  final double? width;
  final double height;
  final EdgeInsets? margin;

  const CustomButton({
    Key? key,
    required this.text,
    this.onPressed,
    this.type = CustomButtonType.primary,
    this.isLoading = false,
    this.fullWidth = true,
    this.icon,
    this.width,
    this.height = 48,
    this.margin,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    // Define styles based on button type
    ButtonStyle getButtonStyle() {
      switch (type) {
        case CustomButtonType.primary:
          return ElevatedButton.styleFrom(
            backgroundColor: theme.primaryColor,
            foregroundColor: Colors.white,
            elevation: 2,
            padding: EdgeInsets.symmetric(horizontal: 24, vertical: 0),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          );

        case CustomButtonType.secondary:
          return ElevatedButton.styleFrom(
            backgroundColor: Colors.grey[200],
            foregroundColor: Colors.black87,
            elevation: 0,
            padding: EdgeInsets.symmetric(horizontal: 24, vertical: 0),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          );

        case CustomButtonType.outline:
          return OutlinedButton.styleFrom(
            foregroundColor: theme.primaryColor,
            side: BorderSide(color: theme.primaryColor),
            padding: EdgeInsets.symmetric(horizontal: 24, vertical: 0),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          );

        case CustomButtonType.text:
          return TextButton.styleFrom(
            foregroundColor: theme.primaryColor,
            padding: EdgeInsets.symmetric(horizontal: 24, vertical: 0),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          );
      }
    }

    Widget buttonChild = Row(
      mainAxisSize: MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (isLoading)
          SizedBox(
            width: 20,
            height: 20,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              valueColor: AlwaysStoppedAnimation<Color>(
                type == CustomButtonType.primary ? Colors.white : theme.primaryColor,
              ),
            ),
          )
        else ...[
          if (icon != null) ...[
            Icon(icon),
            SizedBox(width: 8),
          ],
          Text(
            text,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ],
    );

    Widget button;
    switch (type) {
      case CustomButtonType.primary:
      case CustomButtonType.secondary:
        button = ElevatedButton(
          onPressed: isLoading ? null : onPressed,
          style: getButtonStyle(),
          child: buttonChild,
        );
        break;
      case CustomButtonType.outline:
        button = OutlinedButton(
          onPressed: isLoading ? null : onPressed,
          style: getButtonStyle(),
          child: buttonChild,
        );
        break;
      case CustomButtonType.text:
        button = TextButton(
          onPressed: isLoading ? null : onPressed,
          style: getButtonStyle(),
          child: buttonChild,
        );
        break;
    }

    return Container(
      width: fullWidth ? double.infinity : width,
      height: height,
      margin: margin,
      child: button,
    );
  }
}

// Loading Button
class LoadingButton extends StatelessWidget {
  final bool isLoading;
  final VoidCallback onPressed;
  final String text;
  final String loadingText;

  const LoadingButton({
    Key? key,
    required this.isLoading,
    required this.onPressed,
    required this.text,
    this.loadingText = 'Please wait...',
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return CustomButton(
      text: isLoading ? loadingText : text,
      onPressed: isLoading ? null : onPressed,
      isLoading: isLoading,
    );
  }
}

// Icon Button with Text
class IconTextButton extends StatelessWidget {
  final IconData icon;
  final String text;
  final VoidCallback onPressed;
  final CustomButtonType type;

  const IconTextButton({
    Key? key,
    required this.icon,
    required this.text,
    required this.onPressed,
    this.type = CustomButtonType.primary,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return CustomButton(
      text: text,
      onPressed: onPressed,
      icon: icon,
      type: type,
    );
  }
}
